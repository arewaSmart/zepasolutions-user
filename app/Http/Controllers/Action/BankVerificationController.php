<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Traits\ActiveUsers;
use App\Traits\KycVerify;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankVerificationController extends Controller
{
    use ActiveUsers;
    use KycVerify;

    protected $loginUserId;

    // Constructor to initialize the property
    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    // Show BVN Page
    public function show(Request $request)
    {

        // Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        // Check if user is Pending, Rejected, or Verified KYC
        $status = $this->is_verified();

        if ($status == 'Pending') {
            return redirect()->route('verification.kyc');

        } elseif ($status == 'Submitted') {
            return view('kyc-status')->with(compact('status'));

        } elseif ($status == 'Rejected') {
            return view('kyc-status')->with(compact('status'));
        } else {

            // Notification Data
            $notifications = Notification::all()->where('user_id', $this->loginUserId)
                ->sortByDesc('id')
                ->where('status', 'unread')
                ->take(3);

            // Notification Count
            $notifycount = 0;
            $notifycount = Notification::all()
                ->where('user_id', $this->loginUserId)
                ->where('status', 'unread')
                ->count();

            // BVN Verification Services Fee
            $ServiceFee = 0;
            $ServiceFee = Services::where('service_code', '104')->first();

            return view('bank-verify')
                ->with(compact('notifications'))
                ->with(compact('ServiceFee'))
                ->with(compact('notifycount'));
        }
    }

    public function retrieveBank(Request $request)
    {

        $request->validate([
            'accountNumber' => 'required|numeric|digits:10',
            'bankcode' => ['required', 'string'],
        ]);

        // Bank Services Fee
        $ServicesFee = 0;
        $ServicesFee = Services::where('service_code', '104')->first();
        $ServicesFee = $ServicesFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServicesFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {
                $client = new \GuzzleHttp\Client;

                $response = $client->post(env('endpoint_v1').'/bank_account/advance', [
                    'form_params' => [
                        'number' => $request->accountNumber,
                        'bank_code' => $request->bankcode,
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                        'app-id' => env('appId'),
                        'content-type' => 'application/x-www-form-urlencoded',
                        'x-api-key' => env('xApiKey'),
                    ],
                ]);

                $responseBody = $response->getBody()->getContents();

                $data = $this->formatAndDecodeJson($responseBody);

                if ($data['status'] == true) {

                    $balance = $wallet->balance - $ServicesFee;

                    $affected = Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

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

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'Bank Account Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦'.number_format($ServicesFee, 2),
                        'amount' => $ServicesFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    // Return Json response
                    return json_encode(['status' => $data['status'], 'data' => $data]);

                } elseif ($data['status'] == false) {
                    return response()->json([
                        'status' => 'Failed',
                        'errors' => ['Verification failed.'],
                    ], 422);

                }

            } catch (RequestException $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['Request failed cannot connect to server, please try again later. '],
                ], 422);
            }

        }
    }

    public function genBankCodes()
    {
        $client = new \GuzzleHttp\Client;

        $response = $client->request('GET', 'https://api.prembly.com/identitypass/verification/bank_account/bank_code', [
            'headers' => [
                'accept' => 'application/json',
                'app_id' => '5428eaa0-416a-49b8-8f34-2eca8d16e2e6',
                'x-api-key' => 'sandbox_sk_1be69a4010a26ea2e114399000d45a934c590bb',
            ],
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $bank) {
                $name = $bank['name'] ?? null;
                $code = $bank['code'] ?? null;

                // Insert into database if both name and code are available
                if ($name && $code) {
                    DB::table('bank_codes')->updateOrInsert(
                        ['code' => $code],  // Condition to check if record exists
                        ['name' => $name, 'code' => $code,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()]  // Values to update or insert
                    );
                }
            }
        }
    }

    public function fetchBanks()
    {
        // Fetch bank codes from the database
        $banks = DB::table('bank_codes')->select(['name', 'code'])->get();

        // Return the bank codes as a JSON response
        return response()->json($banks);
    }

    private function formatAndDecodeJson($jsonString)
    {
        // Remove newline characters and excessive whitespace
        $formattedString = preg_replace('/\s+/', ' ', $jsonString);

        // Fix potential issues with escaped quotes
        $formattedString = str_replace('\"', '"', $formattedString);

        // Trim leading and trailing whitespace
        $formattedString = trim($formattedString);

        // Decode the JSON string
        return json_decode($formattedString, true);
    }
}
