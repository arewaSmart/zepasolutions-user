<?php

namespace App\Http\Controllers;

use App\Helpers\signatureHelper;
use App\Mail\Payment_notify_mail;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PaymentWebhookController extends Controller
{


    public function handleWebhook(Request $request)
    {

        $payload = $request->all();

        // Verify the signature
        if (! $this->verifySignature($payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload

        // Sanitize sensitive fields before logging
        $sanitizedPayload = $payload;
        foreach (['sign', 'signature', 'pin', 'password', 'token', 'key'] as $key) {
            if (isset($sanitizedPayload[$key])) {
                $sanitizedPayload[$key] = '********';
            }
        }

        Log::info('Palmpay webhook received Data:', $sanitizedPayload);

        $this->processReservedAccountTransaction($payload);

        return response('success', 200)->header('Content-Type', 'text/plain');
    }

    private function verifySignature($data)
    {

        $sign = $data['sign'];

        $verifyResults = signatureHelper::verify_callback_signature($data, $sign, config('keys.public'));

        if ($verifyResults != true) {
            return false;
        }

        return true;
    }

    private function processReservedAccountTransaction($payload)
    {
        Log::info('[PAYIN]:', $payload);

        $virtualAccountNo = $payload['virtualAccountNo'];
        $orderNo = $payload['orderNo'];
        $amountPaid = $payload['orderAmount'] / 100;
        $payerBankName = $payload['payerBankName'];
        $payerAccountName = $payload['payerAccountName'];
        $service_description = 'Your wallet has been credited with ₦'.number_format($amountPaid, 2);
        $orderStatus = $payload['orderStatus'] ?? null;

        // Only process and credit if order status is successful (status 1)
        if ($orderStatus != 1) {
            Log::warning("[PAYIN]: Webhook received for non-successful order status ({$orderStatus}). Skipping crediting for Order No: ".$orderNo);
            return;
        }

        $response = VirtualAccount::select('user_id')->where('accountNo', $virtualAccountNo)->first();

        if ($response) {
            $this->createTransactionForReservedAccount($response->user_id, $orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus);
        }
    }

    private function updateTransaction($orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus)
    {
        $status = 'Approved'; // Default to Approved if we reached here from a success payload, but respect orderStatus

        if ($orderStatus != 1) {
             // If not 1, maybe it's pending or failed
             // But usually this webhook is only for successful payments in this flow
             $status = 'Pending'; 
        }

        Transaction::where('referenceId', $orderNo)
            ->update([
                'service_type' => 'Wallet Topup',
                'service_description' => $service_description,
                'amount' => $amountPaid,
                'gateway' => $payerBankName,
                'status' => $status,
                'type' => 'credit',
            ]);
    }

    private function insertTransaction($userId, $orderNo, $amountPaid, $payerAccountName, $payerBankName, $service_description)
    {
        Transaction::create([
            'user_id' => $userId,
            'payer_name' => $payerAccountName,
            'referenceId' => $orderNo,
            'service_type' => 'Wallet Topup',
            'service_description' => $service_description,
            'amount' => $amountPaid,
            'gateway' => $payerBankName,
            'status' => 'Approved',
            'type' => 'credit',
        ]);
    }

    private function createTransactionForReservedAccount($userId, $orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus)
    {
        $shouldNotify = false;

        try {
            // Use a database transaction to ensure idempotency
            DB::transaction(function () use ($userId, $orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus, &$shouldNotify) {
                // Check if Transaction Existed in db with lock
                $transaction = Transaction::where('referenceId', $orderNo)->lockForUpdate()->first();

                if ($transaction) {
                    // If it already exists, we only update if it's not already approved/finalized
                    if ($transaction->status !== 'Approved') {
                        $this->updateTransaction($orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus);
                        Log::info('[PAYIN]: Updated existing transaction for Order No: '.$orderNo);
                    } else {
                        Log::info('[PAYIN]: Duplicate webhook received. Transaction already processed for Order No: '.$orderNo);
                    }
                } else {
                    // Prevent duplicate credits of the same amount for the same user within 5 minutes
                    $recentTransaction = Transaction::where('user_id', $userId)
                        ->where('amount', $amountPaid)
                        ->where('service_type', 'Wallet Topup')
                        ->where('status', 'Approved')
                        ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                        ->lockForUpdate()
                        ->first();

                    if ($recentTransaction) {
                        Log::warning("[PAYIN]: Potential duplicate deposit detected. User {$userId} already credited with ₦{$amountPaid} within the last 5 minutes (Recent Ref: {$recentTransaction->referenceId}). Blocking current Ref: {$orderNo}");
                        return;
                    }

                    $this->insertTransaction($userId, $orderNo, $amountPaid, $payerAccountName, $payerBankName, $service_description);
                    $this->updateWalletBalance($userId, $amountPaid);
                    $shouldNotify = true;
                    Log::info('[PAYIN]: New transaction created and wallet updated for Order No: '.$orderNo);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle race-condition duplicate key violations at database level
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                Log::info('[PAYIN]: QueryException caught. Duplicate webhook transaction reference prevented: '.$orderNo);
            } else {
                throw $e;
            }
        }

        // Send notification and email outside the transaction to reduce lock time
        if ($shouldNotify) {
            $this->sendNotificationAndEmail($userId, $amountPaid, $orderNo, $payerBankName, 'Topup');
        }
    }

    private function updateWalletBalance($userId, $amountPaid)
    {
        // Lock the wallet to prevent race conditions during concurrent updates
        $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
        if ($wallet) {
            $wallet->increment('balance', $amountPaid);
            $wallet->increment('deposit', $amountPaid);
            
            Log::info("Wallet balance updated for user {$userId}: +{$amountPaid}");
        } else {
            Log::warning('Wallet not found for user ID: '.$userId);
        }
    }



    private function sendNotificationAndEmail($userId, $amountPaid, $orderNo, $bankName, $type)
    {
        $user = User::find($userId);
        if ($user) {
            $mail_data = [
                'type' => $type,
                'amount' => number_format($amountPaid, 2),
                'ref' => $orderNo,
                'bankName' => $bankName,
            ];

            try {
                Mail::to($user->email)->send(new Payment_notify_mail($mail_data));
            } catch (TransportExceptionInterface $e) {
                Log::error('Error sending email for transaction '.$orderNo.': '.$e->getMessage());
            }

            Notification::create([
                'user_id' => $userId,
                'message_title' => 'Top Up',
                'messages' => 'Wallet TopUp of ₦'.number_format($amountPaid, 2).' was successful.',
            ]);
        }
    }
}
