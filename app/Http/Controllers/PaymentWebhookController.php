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
    public function handlePayout($payload)
    {

        if (! $this->verifySignature($payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $orderId = $payload['orderId'];

        // Use a database transaction to prevent race conditions
        DB::transaction(function () use ($orderId) {
            // Find the specific transaction by referenceId and lock it for update
            $transaction = Transaction::where('referenceId', $orderId)
                ->where('status', 'Pending')
                ->lockForUpdate()
                ->first();

            if ($transaction) {
                $this->processPayoutTransaction($transaction);
            } else {
                Log::info('[PAYOUT]: Transaction already processed or not found for Order ID: '.$orderId);
            }
        });
    }

    public function handleWebhook(Request $request)
    {

        $payload = $request->all();

        // Verify the signature
        if (! $this->verifySignature($payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload

        Log::info('Palmpay webhook received Data:', $payload);

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

        if (isset($payload['orderId']) && isset($payload['transType']) && $payload['transType'] == 41) {

            Log::info('[PAYOUT]:', $payload);

            $this->handlePayout($payload);
        } else {

            Log::info('[PAYIN]:', $payload);

            $virtualAccountNo = $payload['virtualAccountNo'];
            $orderNo = $payload['orderNo'];
            $amountPaid = $payload['orderAmount'] / 100;
            $payerBankName = $payload['payerBankName'];
            $payerAccountName = $payload['payerAccountName'];
            $service_description = 'Your wallet has been credited with ₦'.number_format($amountPaid, 2);
            $orderStatus = $payload['orderStatus'];

            $response = VirtualAccount::select('user_id')->where('accountNo', $virtualAccountNo)->first();

            if ($response) {
                $this->createTransactionForReservedAccount($response->user_id, $orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus);
            }
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
        ]);
    }

    private function createTransactionForReservedAccount($userId, $orderNo, $amountPaid, $payerBankName, $payerAccountName, $service_description, $orderStatus)
    {
        $shouldNotify = false;

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
                $this->insertTransaction($userId, $orderNo, $amountPaid, $payerAccountName, $payerBankName, $service_description);
                $this->updateWalletBalance($userId, $amountPaid);
                $shouldNotify = true;
                Log::info('[PAYIN]: New transaction created and wallet updated for Order No: '.$orderNo);
            }
        });

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

    private function processPayoutTransaction($transaction)
    {
        $userId = $transaction->user_id;
        $amountPaid = $transaction->amount;

        // Fetch wallet with lock
        $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

        if ($wallet) {
            // Deduct from balance
            $wallet->decrement('balance', $amountPaid);
            Log::info("Wallet balance deducted for payout: User {$userId}, Amount {$amountPaid}");
        } else {
            Log::warning('Wallet not found for user ID: '.$userId);
            return; 
        }

        // Update the specific transaction status
        $transaction->update(['status' => 'Approved']);
        Log::info("Transaction {$transaction->referenceId} marked as Approved");
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
