<?php

namespace App\Http\Controllers\Action;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Notes;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Repositories\VirtualAccountRepository;
use App\Repositories\WalletRepository;
use App\Traits\ActiveUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    public function show(Request $request)
    {
        // Enhance later via middleware
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $status = auth()->user()->kyc_status;

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

        $bonus_balance = Wallet::where('user_id', $this->loginUserId)->value('bonus') ?? 0;

        $transactions = Transaction::where('user_id', $this->loginUserId)
            ->orderByDesc('id')
            ->paginate(10);

        $transaction_count = Transaction::where('user_id', $this->loginUserId)->count();

        $newsItems = News::all();

        $note = Notes::where('is_active', 1)->first();

        $notificationsEnabled = Auth::user()->notification;

        $virtual_funding = DB::table('service_statuses')->where('service_id', 5)->first();

        $kycPending = session('kyc_pending', false);

        if ($status == 'Pending') {
            $kycPending = true;
        }

        return view('dashboard', [
            'note' => $note,
            'newsItems' => $newsItems,
            'transactions' => $transactions,
            'transaction_count' => $transaction_count,
            'bonus_balance' => number_format($bonus_balance, 2),
            'wallet_balance' => number_format($wallet_balance, 2),
            'virtual_accounts' => $virtual_accounts,
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
            'virtual_funding' => $virtual_funding,
            'kycPending' => $kycPending,
            'status' => $status,
        ]);
    }

    private function createAccounts($userId)
    {

        $repObj = new WalletRepository;
        $repObj->createWalletAccount($userId);

        $repObj2 = new VirtualAccountRepository;
        $repObj2->createVirtualAccount($userId);
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'bvn' => 'required|numeric|digits:11',
        ]);

        $bvn = $request->input('bvn');

        return $this->verifyBVN($bvn);
    }

    private function verifyBVN($bvn)
    {
        try {

            $bvn_no = $bvn;

            $requestTime = (int) (microtime(true) * 1000);

            $noncestr = noncestrHelper::generateNonceStr();

            $data = [

                'version' => env('VERSION'),
                'nonceStr' => $noncestr,
                'requestTime' => $requestTime,
                'bvn' => $bvn_no,
            ];

            $signature = signatureHelper::generate_signature($data, config('keys.private2'));

            $url = env('Domain').'/api/validator-service/open/bvn/inquire';
            $token = env('BEARER');
            $headers = [
                'Accept: application/json, text/plain, */*',
                'CountryCode: NG',
                "Signature: $signature",
                'Content-Type: application/json',
                "Authorization: Bearer $token",
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

            $data = json_decode($response, true);

            if ($data['respCode'] == 00000000) {

                $data = $data['data'];

                $updateData = [
                    'first_name' => ucwords(strtolower($data['firstName'])),
                    'middle_name' => ucwords(strtolower($data['middleName'])) ?? null,
                    'last_name' => ucwords(strtolower($data['lastName'])),
                    'dob' => $data['birthday'],
                    'gender' => $data['gender'],
                    'kyc_status' => 'Verified',
                    'idNumber' => $bvn_no,
                ];

                if (! empty($data['phoneNumber'])) {
                    $updateData['phone_number'] = $data['phoneNumber'];
                }

                if (! empty($data['photo'])) {
                    $updateData['profile_pic'] = $data['photo'];
                }

                User::where('id', $this->loginUserId)->update($updateData);
                $this->createAccounts($this->loginUserId);

                return redirect()->back()->with('success', 'Your identity verification is complete, and youre all set to explore our services. Thank you for verifying your account!');
            } elseif ($data['respCode'] == 99120020 || $data['respCode'] == 99120024) {

                return redirect()->back()->with('error', 'Invalid or suspended BVN detected. Please update your BVN information and try again.');
            } else {
                return redirect()->back()->with('error', 'An error occurred while making the BVN Verification (System Err)');
            }
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'An error occurred while making the BVN Verification');
        }
    }
}
