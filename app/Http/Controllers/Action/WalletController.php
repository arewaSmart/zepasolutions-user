<?php

namespace App\Http\Controllers\Action;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusHistory;
use App\Models\Notification;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    protected $notificationService;

    protected $transactionService;

    public function __construct(NotificationService $notificationService, TransactionService $transactionService)
    {
        $this->notificationService = $notificationService;
        $this->transactionService = $transactionService;
        $this->loginUserId = Auth::id();
    }

    public function claim()
    {

        // Enhance via middleware later
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = $notifications->count();

        $wallet = Wallet::where('user_id', $this->loginUserId)->first();

        // get all referral bonus
        $bonus_balance = BonusHistory::where('user_id', $this->loginUserId)->sum('amount');

        $unclaimed_balance = $wallet->bonus ?? 0;

        $claimed_balance = $bonus_balance - $unclaimed_balance;

        $notificationsEnabled = Auth::user()->notification;

        $transaction = DB::table('claim_counts')->first();

        $transaction_count = $transaction->transaction_count ?? 5;

        $users = User::where('refferral_id', $this->loginUserId)
            ->withCount('transactions')
            ->paginate(10);

        $userIds = $users->pluck('id');
        $bonusHistories = BonusHistory::whereIn('referred_user_id', $userIds)->get();

        $bonusHistoriesGrouped = $bonusHistories->groupBy('referred_user_id');

        $usersWithBonuses = $users->map(function ($user) use ($bonusHistoriesGrouped) {

            $totalBonusAmount = $bonusHistoriesGrouped->has($user->id)
                ? $bonusHistoriesGrouped->get($user->id)->sum('amount')
                : 0;

            $user->total_bonus_amount = $totalBonusAmount;

            return $user;
        });

        $users->setCollection($usersWithBonuses);

        return view('claim', [
            'claimed_balance' => number_format($claimed_balance, 2),
            'unclaimed_balance' => number_format($unclaimed_balance, 2),
            'bonus_balance' => number_format($bonus_balance, 2),
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
            'transaction_count' => $transaction_count,
            'users' => $users,
        ]);
    }

    public function claimBonus($user_id)
    {

        $count = 0;
        $claim_id = 0;

        $transaction = DB::table('claim_counts')->first();
        $transaction_count = $transaction->transaction_count ?? 5;

        $user = User::where('id', $user_id)->first();
        $count = $user->transactions()->count();
        $claim_id = $user->claim_id;

        if ($user_id == $this->loginUserId) {
            return redirect()->back()->with('error', 'Nice try! But our system is one step ahead!');
        } elseif ($claim_id == 0 && $count >= $transaction_count) {

            $bonus = BonusHistory::where('referred_user_id', $user_id)->first();

            $wallet = Wallet::where('user_id', $bonus->user_id)->first();

            $new_wallet_balance = $wallet->balance + $bonus->amount;
            $new_deposit_balance = $wallet->deposit + $bonus->amount;
            $new_bonus_balance = $wallet->bonus - $bonus->amount;

            Wallet::where('user_id', $bonus->user_id)->update([
                'balance' => $new_wallet_balance,
                'deposit' => $new_deposit_balance,
                'bonus' => $new_bonus_balance,
            ]);

            User::where('id', $user_id)->update(['claim_id' => 1]);

            $this->transactionService->createTransaction($bonus->user_id, $bonus->amount);

            $this->notificationService->createNotification(
                $bonus->user_id,
                'Bonus Claim',
                'Bonus claim to wallet  ₦'.number_format($bonus->amount, 2),
            );

            $successMessage = 'Your bonus has been claimed and added to your main wallet. Congratulations!';

            return redirect()->back()->with('success', $successMessage);
        } else {
            return redirect()->back()->with('error', 'You are not eligible to claim the bonus at this time. Please ensure your referrals have completed the required minimum of 5 transactions to qualify.');
        }
    }

    public function p2p()
    {

        // Enhance via middleware later
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = $notifications->count();
        $notificationsEnabled = Auth::user()->notification;

        return view('p2p', [
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    public function transfer(Request $request)
    {

        $request->validate([
            'Wallet_ID' => 'required|numeric|digits:11',
            'Amount' => 'required|numeric|min:50|max:100000',
        ]);

        $exists = User::where('phone_number', $request->Wallet_ID)->exists();

        if ($exists) {

            if (Auth::user()->phone_number == $request->Wallet_ID) {

                return redirect()->back()->with('error', 'Nice try! But our system is one step ahead!');
            }

            $Receiver_details = User::where('phone_number', $request->Wallet_ID)->first();

            if ($Receiver_details->wallet_is_created == 0) {

                return redirect()->back()->with('error', 'Account is pending KYC Verification!');
            }

            // Check if wallet is funded
            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            $wallet_balance = $wallet->balance;
            $balance = 0;

            if ($wallet_balance < $request->Amount) {
                return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
            } else {

                $balance = $wallet->balance - $request->Amount;

                $affected = Wallet::where('user_id', $this->loginUserId)
                    ->update(['balance' => $balance]);

                // Recivers details

                // get reciever wallet id
                $results = User::where('phone_number', $request->Wallet_ID)->first();

                $wallet = Wallet::where('user_id', $results->id)->first();
                $bal = $wallet->balance + $request->Amount;
                $bal2 = $wallet->deposit + $request->Amount;

                Wallet::where('user_id', $results->id)
                    ->update(['balance' => $bal, 'deposit' => $bal2]);

                // Transactions and notifications

                $referenceno = '';
                srand((float) microtime() * 1000000);
                $gen = '123456123456789071234567890890';
                $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
                $ddesc = '';
                for ($i = 0; $i < 12; $i++) {
                    $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                }

                $payer_name = auth()->user()->first_name.' '.Auth::user()->last_name;
                $payer_email = auth()->user()->email;
                $payer_phone = auth()->user()->phone_number;

                Transaction::insert(
                    [[
                        'user_id' => $this->loginUserId,
                        'payer_name' => $Receiver_details->first_name.' '.$Receiver_details->last_name,
                        'payer_email' => $Receiver_details->email,
                        'payer_phone' => $Receiver_details->phone_number,
                        'referenceId' => $referenceno,
                        'service_type' => 'Wallet Transfer',
                        'service_description' => 'Wallet debitted with ₦'.number_format($request->Amount, 2).'
                        transferred to
                        ('.$Receiver_details->first_name.' '.$Receiver_details->last_name.')',
                        'amount' => $request->Amount,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ], [
                        'user_id' => $results->id,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'Wallet Top up',
                        'service_description' => 'Wallet creditted with ₦'.number_format(
                            $request->Amount,
                            2
                        ).' By ('.$payer_name.')',
                        'amount' => $request->Amount,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]]
                );

                // Notifocation
                // In App Notification
                Notification::insert([
                    [
                        'user_id' => $this->loginUserId,
                        'message_title' => 'Wallet Transfer',
                        'messages' => 'Transfer of ₦'.number_format($request->Amount, 2).' was successful',
                    ],
                    [
                        'user_id' => $results->id,
                        'message_title' => 'Wallet Top up',
                        'messages' => 'Wallet Credited with ₦'.number_format($request->Amount, 2),
                    ],
                ]);

                $successMessage = 'Transfer Successful';

                // Correctly format the link
                $link = '&nbsp; <a href="'.route('reciept', $referenceno).'"><i class="bi bi-download"></i>
                          Download Receipt</a>';

                return redirect()->back()->with('success', $successMessage.' '.$link);
            }
        } else {
            return redirect()->back()->with('error', 'Sorry Wallet ID does not exist !');
        }

        $successMessage = '';

        return redirect()->back()->with('success', $successMessage);
    }

    public function funding()
    {
        // Enhance via middleware later
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = $notifications->count();

        $virtual_accounts = VirtualAccount::where('status', '1')
            ->where('user_id', $this->loginUserId)
            ->take(2)
            ->get();

        $wallet_balance = Wallet::where('user_id', $this->loginUserId)->value('balance') ?? 0;

        $deposit = Wallet::where('user_id', $this->loginUserId)->value('deposit') ?? 0;

        $spent = $deposit - $wallet_balance;

        $notificationsEnabled = Auth::user()->notification;

        $manual_funding = DB::table('service_statuses')->where('service_id', 3)->first();

        $online_funding = DB::table('service_statuses')->where('service_id', 4)->first();

        $virtual_funding = DB::table('service_statuses')->where('service_id', 5)->first();

        return view('funding', [
            'deposit' => number_format($deposit, 2),
            'wallet_balance' => number_format($wallet_balance, 2),
            'virtual_accounts' => $virtual_accounts,
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
            'spent' => $spent,
            'manual_funding' => $manual_funding,
            'online_funding' => $online_funding,
            'virtual_funding' => $virtual_funding,
        ]);
    }

    public function getReciever(Request $request)
    {

        //  $request->validate([
        //         'walletID' => 'required|numeric|digits:11',
        //     ]);

        $query = User::select([
            DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"),
        ])->where('phone_number', $request->walletID)->get();

        $reciever = $query->first();

        if ($reciever != null) {

            if ($reciever['full_name'] == null) {

                return response()->json('kyc');
            } else {
                return response()->json($reciever['full_name']);
            }
        } else {
            return null;
        }
    }

    public function getUserdetails(Request $request)
    {

        $query = User::where('phone_number', $request->walletID);

        $reciever = $query->first();

        if ($reciever) {
            return response()->json($reciever);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function verify(Request $request)
    {

        $pmethod = $request->pmethod;

        // Paystack Channel
        if ($pmethod == 'paystack') {

            return response()->json(['code' => '201']);
        }
        // Monie Point Channel
        elseif ($pmethod == 'moniepoint') {

            $payer_name = Auth::user()->first_name.' '.Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            // $user = Transaction::insert([
            // 'user_id' => $this->loginUserId,
            // //'payerid' => '',
            // 'payer_name' => $payer_name,
            // 'payer_email' => $payer_email,
            // 'payer_phone' => $payer_phone,
            // 'referenceId' => $request->ref,
            //  'service_type' => 'Wallet Topup',
            // 'service_description' => 'Your wallet have been credited with ₦'.number_format($request->amt, 2),
            // 'amount' => $request->amt,
            // 'type' => '',
            // 'gateway' => 'Monnify',
            // 'status' => 'Pending',
            // 'created_at' => Carbon::now(),
            // 'updated_at' => Carbon::now(),
            // ]);

            // //Update Wallet balance
            // $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            // $balance = $wallet->balance + $request->amt;
            // $deposit = $wallet->deposit + $request->amt;

            // $affected = Wallet::where('user_id', $this->loginUserId)
            // ->update(['balance' =>$balance,
            // 'deposit' => $deposit]);

            // Correctly format the link
            $link = route('reciept', $request->ref);

            return response()->json(['code' => '200', 'link' => $link]);
        }
    }

    public function showPayout()
    {

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->latest()
            ->take(3)
            ->get();

        $notifyCount = $notifications->count();

        $notificationsEnabled = Auth::user()->notification;

        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $walletBalance = number_format($wallet->balance, 2);

        $getCharges = Services::where('service_code', '150')
            ->where('category', 'charges')
            ->where('type', 'PAYOUT')
            ->first();

        $charges = $getCharges->amount;

        return view('payout', compact('notifications', 'notifyCount', 'notificationsEnabled', 'walletBalance', 'charges'));
    }

    public function isServiceEnabled($serviceId)
    {

        $serviceStatus = DB::table('service_statuses')->where('service_id', $serviceId)->first();

        if ($serviceStatus && $serviceStatus->is_enabled) {
            return true;
        }

        return false;
    }

    public function transactionLimitCheck($amount)
    {

        // Current User
        $userId = Auth::id();

        // Get current date for daily transfers
        $currentDate = Carbon::now()->format('Y-m-d');

        // Count transfers for the user on the current date
        $dailyTransferAmount = DB::table('user_transactions')
            ->where('user_id', $userId)
            ->whereDate('transaction_date', $currentDate)
            ->where('transaction_type', 'transfer')
            ->sum('amount');

        // Get user's daily limit for transfers
        $user = User::find($userId);
        $dailyLimit = $user->daily_limit;

        // Compare the total daily transaction amount with the user's daily limit
        if (($dailyTransferAmount) + $amount > $dailyLimit) {
            return false;
        }

        return true;
    }

    public function checkWalletBalance($amount)
    {
        $userId = Auth::id();

        $wallet = Wallet::where('user_id', $userId)->first();

        if (! $wallet) {
            return ['status' => false, 'message' => 'Wallet not found.'];
        }

        $wallet_balance = $wallet->balance;

        if ($wallet_balance < $amount) {
            return ['status' => false, 'message' => 'Sorry, Wallet not sufficient for transaction!'];
        }

        return ['status' => true, 'balance' => $wallet_balance];
    }

    public function payout(Request $request)
    {
        // Request validation checks with custom messages
        $request->validate(
            [
                'amount' => 'required|numeric|min:10',
                'bankCode' => 'required|numeric|digits:6',
                'accountNumber' => 'required|numeric|min:10',
            ],
            [
                'amount.required' => 'Please enter the amount to transfer.',
                'amount.numeric' => 'The amount must be a valid number.',
                'amount.min' => 'The minimum transfer amount is 50 Naira.',
                'bankCode.required' => 'Please select a bank code.',
                'bankCode.numeric' => 'The bank code must be a valid number.',
                'bankCode.digits' => 'The bank code must be exactly 6 digits.',
                'accountNumber.required' => 'Please enter an account number.',
                'accountNumber.numeric' => 'The account number must be a valid number.',
                'accountNumber.min' => 'The account number must be at least 10 digits.',
            ]
        );

        $amount = $request->amount;

        $getCharges = Services::where('service_code', '150')
            ->where('category', 'charges')
            ->where('type', 'PAYOUT')
            ->first();

        $charges = $getCharges->amount;

        // Check if payout is enbaled
        if (! $this->isServiceEnabled(2)) {
            return redirect()->back()->with('error', 'Sorry, bank transfers are temporarily unavailable. Please try again later.');
        }

        $final_amount = $amount + $charges;
        // Transaction Limit Check
        if (! $this->transactionLimitCheck($final_amount)) {

            return redirect()->back()->with('error', 'You\'ve reached your daily transfer limit. Try again tomorrow.');
        }

        // Check if account balance is sufficent
        $walletCheck = $this->checkWalletBalance($final_amount);

        if (! $walletCheck['status']) {
            return redirect()->back()->with('error', $walletCheck['message']);
        }

        $walletBalance = $walletCheck['balance'];

        $payer_name = auth()->user()->first_name.' '.Auth::user()->last_name;
        $payer_phone = auth()->user()->phone_number;
        $payer_email = auth()->user()->email;
        $payer_phone = auth()->user()->phone_number;

        $amount_to_post = $amount * 100;

        try {

            $requestTime = (int) (microtime(true) * 1000);

            $noncestr = noncestrHelper::generateNonceStr();
            $orderId = noncestrHelper::generateOrderId();

            $data = [
                'requestTime' => $requestTime,
                'version' => env('VERSION'),
                'nonceStr' => $noncestr,
                'orderId' => $orderId,
                'payeeName' => $payer_name,
                'payeeBankCode' => $request->bankCode,
                'payeeBankAccNo' => $request->accountNumber,
                'payeePhoneNo' => $payer_phone,
                'amount' => $amount_to_post,
                'currency' => 'NGN',
                'notifyUrl' => env('NOTIFY_URL'),
            ];
            if ($request->filled('reference')) {
                $data['remark'] = $request->reference;
            }

            $signature = signatureHelper::generate_signature($data, config('keys.private'));

            $url = env('BASE_URL3').'api/v2/merchant/payment/payout';
            $token = env('BEARER_TOKEN');
            $headers = [
                'Accept: application/json, text/plain, */*',
                'CountryCode: NG',
                "Authorization: Bearer $token",
                "Signature: $signature",
                'Content-Type: application/json',
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            // Decode the JSON response to an associative array
            $response = json_decode($response, true);

            // Check if decoding was successful
            if ($response === null) {
                return redirect()->back()->with('error', 'Failed Invalid response.');
            }

            // Check for success
            if (isset($response['respCode']) && $response['respCode'] === '00000000') {
                if (isset($response['data']['status']) && $response['data']['status'] === 1) {

                    $referenceno = $response['data']['orderId'];
                    $newBalance = $walletBalance - ($amount + $charges); // new balance plus transfer fee

                    // Update wallet balance
                    Wallet::where('user_id', $this->loginUserId)->update([
                        'balance' => $newBalance,
                    ]);

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'Payout',
                        'service_description' => "Wallet Payout to {$request->accountNumber}".($request->reference ? "|desc|{$request->reference}" : '|NA'),
                        'amount' => $final_amount,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    // In App Notification
                    Notification::create([
                        'user_id' => $this->loginUserId,
                        'message_title' => 'Payout',
                        'messages' => 'Payout from wallet  ₦'.number_format($final_amount, 2),
                    ]);

                    // update user transaction count
                    DB::table('user_transactions')->insert([
                        'user_id' => $this->loginUserId,
                        'amount' => $final_amount,
                        'transaction_date' => Carbon::now()->format('Y-m-d'),
                        'transaction_type' => 'transfer',
                    ]);

                    $successMessage = 'Transfer Successful';

                    $link = '&nbsp; <a href="'.route('reciept', $referenceno).'"><i class="bi bi-download"></i>
                 Download Receipt</a>';

                    return redirect()->back()->with('success', $successMessage.' '.$link);
                }
            }

            if (isset($response['respMsg']) && $response['respMsg'] === 'success') {

                $referenceno = $response['data']['orderId'];
                // $newBalance = $walletBalance - ($amount + $charges); //new balance plus transfer fee

                // Update wallet balance
                // Wallet::where('user_id', $this->loginUserId)->update([
                //    'balance' => $newBalance,
                //  ]);

                Transaction::create([
                    'user_id' => $this->loginUserId,
                    'payer_name' => $payer_name,
                    'payer_email' => $payer_email,
                    'payer_phone' => $payer_phone,
                    'referenceId' => $referenceno,
                    'service_type' => 'Payout',
                    'service_description' => "Wallet Payout to {$request->accountNumber}".($request->reference ? "|desc|{$request->reference}" : '|NA'),
                    'amount' => $final_amount,
                    'gateway' => 'Wallet',
                    'status' => 'Pending',
                ]);

                // In App Notification
                Notification::create([
                    'user_id' => $this->loginUserId,
                    'message_title' => 'Payout',
                    'messages' => 'Payout from wallet  ₦'.number_format($final_amount, 2),
                ]);

                // update user transaction count
                DB::table('user_transactions')->insert([
                    'user_id' => $this->loginUserId,
                    'amount' => $final_amount,
                    'transaction_date' => Carbon::now()->format('Y-m-d'),
                    'transaction_type' => 'transfer',
                ]);

                $successMessage = 'Transfer Successful';

                $link = '&nbsp; <a href="'.route('reciept', $referenceno).'"><i class="bi bi-download"></i>
                     Download Receipt</a>';

                return redirect()->back()->with('success', $successMessage.' '.$link);
            }

            // Failure logic
            $errorMessage = $response['respMsg'] ?? 'Transaction failed.';

            return redirect()->back()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error in get Account Details: '.$e->getMessage());
            $errorMessage = 'Transaction failed.'.$response['respMsg'] ?? 'Transaction failed.';

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:transfer,bank_deposit',
            'senders_bank' => 'required|string',
            'senders_name' => 'required|string',
            'date' => 'required|date',
        ]);

        $count = DB::table('manual_funding_request')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 5) {

            return response()->json(['code' => 11, 'message' => 'Note: You have reached the maximum limit of ('.$count.') Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.']);
        }

        $referenceno = '';
        srand((float) microtime() * 1000000);
        $gen = '123456123456789071234567890890';
        $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';

        for ($i = 0; $i < 12; $i++) {
            $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
        }

        $payer_name = auth()->user()->first_name.' '.Auth::user()->last_name;
        $payer_email = auth()->user()->email;
        $payer_phone = auth()->user()->phone_number;

        $trx_id = Transaction::create([
            'user_id' => $this->loginUserId,
            'payer_name' => $payer_name,
            'payer_email' => $payer_email,
            'payer_phone' => $payer_phone,
            'referenceId' => $referenceno,
            'service_type' => 'Manual Funding',
            'service_description' => 'Manual Wallet funding of '.number_format($request->amount, 2),
            'amount' => $request->amount,
            'gateway' => 'Wallet',
            'status' => 'Pending',
        ]);

        $trx_id = $trx_id->id;

        DB::table('manual_funding_request')->insert([
            'user_id' => auth()->id(),
            'tnx_id' => $trx_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'senders_bank' => $request->senders_bank,
            'senders_name' => $request->senders_name,
            'date' => $request->date,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Request submitted successfully!']);
    }
}
