<?php

namespace App\Http\Controllers;

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

        $user = auth()->user();

        // Self-heal wallet or virtual account if they were not successfully created during registration
        if ($status == 'Verified' && $user->wallet_is_created == 0) {
            $repObj = new WalletRepository;
            $repObj->createWalletAccount($this->loginUserId);
        }

        if ($status == 'Verified' && $user->vwallet_is_created == 0) {
            $repObj2 = new VirtualAccountRepository;
            $repObj2->createVirtualAccount($this->loginUserId);
        }

        $virtual_accounts = VirtualAccount::where('user_id', $this->loginUserId)
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
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female',
            'phone_number' => 'nullable|numeric|digits:11|unique:users,phone_number,' . Auth::id(),
        ]);

        $bvn = $request->input('bvn');

        return $this->verifyBVN($bvn, $request);
    }

    private function verifyBVN($bvn, Request $request)
    {
        try {
            $apiKey = env('AREWA_API_TOKEN');
            $apiBaseUrl = env('AREWA_BASE_URL');
            $apiUrl = rtrim($apiBaseUrl, '/') . '/bvn/verify';

            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->withoutVerifying()
                ->acceptJson()
                ->post($apiUrl, [
                    'bvn' => $bvn,
                ]);

            $decodedData = $response->json();

            if (!$response->successful() || (isset($decodedData['status']) && $decodedData['status'] === 'error')) {
                return redirect()->back()->with('error', 'API Error: ' . ($decodedData['message'] ?? 'Unknown error occurred.'))->withInput();
            }

            $status = $decodedData['status'] ?? 'UNKNOWN';

            if ($status === 'success') {
                $apiData = $decodedData['data'] ?? [];

                $firstName = $apiData['firstName'] ?? ($apiData['first_name'] ?? '');
                $lastName = $apiData['lastName'] ?? ($apiData['last_name'] ?? '');
                $middleName = $apiData['middleName'] ?? ($apiData['middle_name'] ?? '');
                $dob = $apiData['dob'] ?? ($apiData['birthday'] ?? '');
                $gender = $apiData['gender'] ?? '';
                $phone = $apiData['phoneNumber'] ?? ($apiData['phone'] ?? '');

                // Fallback to request input or database user values if API didn't return them
                $currentUserObj = Auth::user();
                if (empty($firstName)) {
                    $firstName = $request->input('first_name') ?: ($currentUserObj->first_name ?? '');
                }
                if (empty($lastName)) {
                    $lastName = $request->input('last_name') ?: ($currentUserObj->last_name ?? '');
                }
                if (empty($middleName)) {
                    $middleName = $request->input('middle_name') ?: ($currentUserObj->middle_name ?? '');
                }
                if (empty($dob)) {
                    $dob = $request->input('dob') ?: ($currentUserObj->dob ?? '');
                }
                if (empty($gender)) {
                    $gender = $request->input('gender') ?: ($currentUserObj->gender ?? 'Male');
                }
                if (empty($phone)) {
                    $phone = $request->input('phone_number') ?: ($currentUserObj->phone_number ?? '');
                }

                $updateData = [
                    'first_name' => ucwords(strtolower($firstName)),
                    'middle_name' => $middleName ? ucwords(strtolower($middleName)) : null,
                    'last_name' => ucwords(strtolower($lastName)),
                    'dob' => $dob,
                    'gender' => $gender,
                    'kyc_status' => 'Verified',
                    'idNumber' => $bvn,
                ];

                if (! empty($phone)) {
                    $updateData['phone_number'] = $phone;
                }

                if (! empty($apiData['photo'] ?? '')) {
                    $updateData['profile_pic'] = $apiData['photo'] ?? '';
                }

                User::where('id', $this->loginUserId)->update($updateData);
                $this->createAccounts($this->loginUserId);

                return redirect()->back()->with('success', 'Your identity verification is complete, and you\'re all set to explore our services. Thank you for verifying your account!');
            } else {
                return redirect()->back()->with('error', $decodedData['message'] ?? 'Verification failed.')->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while making the BVN Verification: ' . $e->getMessage())->withInput();
        }
    }

    public function saveProfileKyc(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u',
            'last_name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u',
            'middle_name' => 'nullable|string|max:255|regex:/^[\pL\s\-]+$/u',
            'dob' => 'required|date',
            'gender' => 'required|string|in:Male,Female',
            'phone_number' => 'required|numeric|digits:11|unique:users,phone_number,' . Auth::id(),
        ]);

        // Age limit check: must be 16 or above
        $dobObject = new \DateTime(date('Y-m-d', strtotime($request->dob)));
        $nowObject = new \DateTime;

        if ($dobObject->diff($nowObject)->y < 16) {
            return redirect()->back()->withErrors(['dob' => 'Age limit must be 16 or Above.'])->withInput();
        }

        $userId = Auth::id();
        User::where('id', $userId)->update([
            'first_name' => ucwords(strtolower($request->first_name)),
            'middle_name' => $request->middle_name ? ucwords(strtolower($request->middle_name)) : null,
            'last_name' => ucwords(strtolower($request->last_name)),
            'dob' => $request->dob,
            'gender' => $request->gender,
            'phone_number' => $request->phone_number,
            'kyc_status' => 'Verified',
        ]);

        // Create wallet
        $repObj = new \App\Repositories\WalletRepository;
        $repObj->createWalletAccount($userId);

        return redirect()->back()->with('success', 'Your profile information has been saved successfully!');
    }
}
