<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusHistory;
use App\Models\Notification;
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
use Illuminate\Support\Facades\Hash;

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
        if ($user_id == $this->loginUserId) {
            return redirect()->back()->with('error', 'Nice try! But our system is one step ahead!');
        }

        try {
            DB::transaction(function () use ($user_id) {
                $transaction = DB::table('claim_counts')->first();
                $transaction_count = $transaction->transaction_count ?? 5;

                // Lock the user record for update to prevent concurrent claims
                $user = User::where('id', $user_id)->lockForUpdate()->first();
                if (!$user) {
                    throw new \Exception('Referral user not found.');
                }

                $count = $user->transactions()->count();
                $claim_id = $user->claim_id;

                if ($claim_id != 0 || $count < $transaction_count) {
                    throw new \Exception('You are not eligible to claim the bonus at this time. Please ensure your referrals have completed the required minimum of 5 transactions to qualify.');
                }

                $bonus = BonusHistory::where('referred_user_id', $user_id)->first();
                if (!$bonus) {
                    throw new \Exception('Bonus history record not found.');
                }

                // Lock recipient wallet for update
                $wallet = Wallet::where('user_id', $bonus->user_id)->lockForUpdate()->first();
                if (!$wallet) {
                    throw new \Exception('Recipient wallet not found.');
                }

                // Perform updates
                $wallet->increment('balance', $bonus->amount);
                $wallet->increment('deposit', $bonus->amount);
                $wallet->decrement('bonus', $bonus->amount);

                $user->update(['claim_id' => 1]);

                $this->transactionService->createTransaction($bonus->user_id, $bonus->amount);

                $this->notificationService->createNotification(
                    $bonus->user_id,
                    'Bonus Claim',
                    'Bonus claim to wallet  ₦'.number_format($bonus->amount, 2),
                );
            });

            return redirect()->back()->with('success', 'Your bonus has been claimed and added to your main wallet. Congratulations!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
            'Wallet_ID' => 'required|string',
            'Amount' => 'required|numeric|min:100',
            'pin' => 'required|digits:4',
        ]);

        $sender = Auth::user();
        $recipientWalletId = $request->Wallet_ID;
        $amount = (float) $request->Amount;

        // 1. PIN verification
        if (!Hash::check($request->pin, $sender->pin)) {
            return redirect()->back()->with('error', 'Incorrect Transaction PIN.');
        }

        // 2. Prevent transferring to self
        if ($sender->phone_number === $recipientWalletId) {
            return redirect()->back()->with('error', 'You cannot transfer funds to your own wallet.');
        }

        // 3. Find recipient
        $recipient = User::where('phone_number', $recipientWalletId)->first();
        if (!$recipient) {
            return redirect()->back()->with('error', 'Recipient Wallet ID not found.');
        }

        // 4. Check sender balance
        $senderWallet = Wallet::where('user_id', $sender->id)->first();
        if (!$senderWallet || $senderWallet->balance < $amount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance. You need NGN ' . number_format($amount, 2));
        }

        // 5. Database transaction to execute the transfer safely
        try {
            DB::transaction(function () use ($sender, $recipient, $senderWallet, $amount) {
                // Deduct from sender
                $senderWallet->decrement('balance', $amount);

                // Add to recipient
                $recipientWallet = Wallet::firstOrCreate(
                    ['user_id' => $recipient->id],
                    ['balance' => 0.00, 'status' => 'active']
                );
                $recipientWallet->increment('balance', $amount);

                // Generate a unique reference
                $reference = 'P2P-' . strtoupper(bin2hex(random_bytes(6)));

                // Create debit transaction for sender
                Transaction::create([
                    'user_id' => $sender->id,
                    'referenceId' => $reference,
                    'transaction_ref' => $reference,
                    'service_type' => 'P2P Transfer (Debit)',
                    'service_description' => "Transferred ₦" . number_format($amount, 2) . " to " . $recipient->first_name . " " . $recipient->last_name . " (" . $recipient->phone_number . ")",
                    'amount' => $amount,
                    'type' => 'debit',
                    'gateway' => 'Wallet',
                    'status' => 'Approved',
                    'payer_name' => $sender->first_name . ' ' . $sender->last_name,
                    'payer_phone' => $sender->phone_number,
                    'payer_email' => $sender->email,
                    'performed_by' => $sender->first_name . ' ' . $sender->last_name,
                ]);

                // Create credit transaction for recipient
                Transaction::create([
                    'user_id' => $recipient->id,
                    'referenceId' => $reference,
                    'transaction_ref' => $reference,
                    'service_type' => 'P2P Transfer (Credit)',
                    'service_description' => "Received ₦" . number_format($amount, 2) . " from " . $sender->first_name . " " . $sender->last_name . " (" . $sender->phone_number . ")",
                    'amount' => $amount,
                    'type' => 'credit',
                    'gateway' => 'Wallet',
                    'status' => 'Approved',
                    'payer_name' => $sender->first_name . ' ' . $sender->last_name,
                    'payer_phone' => $sender->phone_number,
                    'payer_email' => $sender->email,
                    'performed_by' => $sender->first_name . ' ' . $sender->last_name,
                ]);

                // Create notification for sender
                Notification::create([
                    'user_id' => $sender->id,
                    'message_title' => 'P2P Transfer',
                    'messages' => "P2P transfer of ₦" . number_format($amount, 2) . " to " . $recipient->first_name . " was successful.",
                ]);

                // Create notification for recipient
                Notification::create([
                    'user_id' => $recipient->id,
                    'message_title' => 'P2P Credit',
                    'messages' => "Your wallet was credited with ₦" . number_format($amount, 2) . " from " . $sender->first_name . ".",
                ]);
            });

            return redirect()->back()->with('success', "Transfer of ₦" . number_format($amount, 2) . " to " . $recipient->first_name . " was successful!");

        } catch (\Exception $e) {
            Log::error('P2P Transfer error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred during transfer. Please try again.');
        }
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

        $virtual_accounts = VirtualAccount::where('user_id', $this->loginUserId)
            ->take(2)
            ->get();

        $wallet = Wallet::where('user_id', $this->loginUserId)->first();

        $wallet_balance = $wallet->balance ?? 0;
        $hold_balance = $wallet->hold_balance ?? 0;
        $deposit = $wallet->deposit ?? 0;

        $spent = max(0, $deposit - $wallet_balance - $hold_balance);

        $notificationsEnabled = Auth::user()->notification;

        return view('funding', [
            'deposit' => number_format($deposit, 2),
            'wallet_balance' => number_format($wallet_balance, 2),
            'hold_balance' => number_format($hold_balance, 2),
            'virtual_accounts' => $virtual_accounts,
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
            'spent' => $spent,
        ]);
    }

    public function getReciever(Request $request)
    {

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
}

