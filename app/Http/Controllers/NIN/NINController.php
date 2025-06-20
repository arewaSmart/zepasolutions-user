<?php

namespace App\Http\Controllers\NIN;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\Verification;
use App\Models\Wallet;
use App\Traits\ActiveUsers;
use App\Traits\KycVerify;
use App\Repositories\NIN_PDF_Repository;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use Illuminate\Support\Facades\Log;

class NINController extends Controller
{

    use ActiveUsers;

    protected $loginUserId;
    protected $walletService;

    const RESP_STATUS_SUCCESS = true;
    const RESP_MESSAGE = null;

    public function __construct(WalletService $walletService)
    {
        $this->loginUserId = Auth::id();
        $this->walletService = $walletService;
    }

    public function ninV2Form(Request $request)
    {
        $loginUserId = $this->loginUserId;

        $notifications = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->count();

        $serviceCodes = ['115', '116', '159'];
        $services = Services::whereIn('service_code', $serviceCodes)->get()->keyBy('service_code');

        $ServiceFee = $services->get('159');
        $standard_nin_fee = $services->get('115');
        $premium_nin_fee = $services->get('116');

        $notificationsEnabled = Auth::user()->notification;

        return view('nin-verify2', compact(
            'notifications',
            'notifyCount',
            'ServiceFee',
            'standard_nin_fee',
            'premium_nin_fee',
            'notificationsEnabled'
        ));
    }

    public function show(Request $request)
    {

        //Enhance via middleware later
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

        $serviceCodes = [
            'verification' => '113',
            'regular' => '114',
            'standard' => '115',
            'premium' => '116',
            'tracking' => '154',
            'demo' => '172',
            'basic'=>'173'
        ];

        $notificationsEnabled = Auth::user()->notification;

        $services = Services::whereIn('service_code', array_values($serviceCodes))->get()->keyBy('service_code');

        $ServiceFee = $services[$serviceCodes['verification']] ?? null;
        $regular_nin_fee = $services[$serviceCodes['regular']] ?? null;
        $standard_nin_fee = $services[$serviceCodes['standard']] ?? null;
        $basic_nin_fee = $services[$serviceCodes['basic']] ?? null;
        $premium_nin_fee = $services[$serviceCodes['premium']] ?? null;
        $trackingServiceFee = $services[$serviceCodes['tracking']] ?? null;
        $demographicServiceFee = $services[$serviceCodes['demo']] ?? null;

        
        $view = match (true) {
            $request->route()->named('nin-phone') => 'nin-phone',
            $request->route()->named('nin-track') => 'nin-track',
            $request->route()->named('nin-demo') => 'nin-demo',
            default => 'nin-verify',
        };

        $data = [
            'notifications' => $notifications,
            'ServiceFee' => $ServiceFee,
            'regular_nin_fee' => $regular_nin_fee,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ];

        if ($view === 'nin-phone' || $view === 'nin-verify' ||$view === 'nin-demo') {
            $data['standard_nin_fee'] = $standard_nin_fee ?? null;
            $data['premium_nin_fee'] = $premium_nin_fee ?? null;
            $data['basic_nin_fee'] = $basic_nin_fee ?? null;
            $data['demographicServiceFee'] = $demographicServiceFee ?? null;
        }

        if ($view === 'nin-track') {
            $data['trackingServiceFee'] = $trackingServiceFee ?? null;
        }
        

        return view($view, $data);
    }

    public function ninV2Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '159')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $requestTime = (int) (microtime(true) * 1000);

                $noncestr = noncestrHelper::generateNonceStr();

                $data = [

                    'version' => env('VERSION'),
                    'nonceStr' => $noncestr,
                    'requestTime' => $requestTime,
                    'nin' => $request->nin,
                ];

                $signature = signatureHelper::generate_signature($data, config('keys.private2'));

                $url = env('Domain') . '/api/validator-service/open/nin/inquire';
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
                    throw new \Exception('cURL Error: ' . curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                // Decode the JSON response to an associative array
                $data = json_decode($response, true);

                if ($data['respCode'] == 00000000) {

                    $this->processResponseData3($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';
                    $ddesc = '';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }

                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "verification_v2");

                    //Return Json response
                    return json_encode(['status' => 'success', 'data' => $data]);
                } else if ($data['respCode'] == 99120010) {


                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
                    $ddesc = '';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }
                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format(
                            $ServiceFee,
                            2
                        ),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "verification_v2");

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninDemoRetrieve(Request $request)
    {

        $request->validate([
            'gender' => ['required', 'in:MALE,FEMALE'],
            'dob' => ['required', 'date'],
            'lastName' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
        ]);

        //NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Services::where('service_code', '172')
            ->where('status', 'enabled')
            ->first();

        if (!$ServiceFee)
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = [
                    'firstName' => $request->input('firstName'),
                    'lastName' => $request->input('lastName'),
                    'dob' => $request->input('dob'),
                    'gender' => $request->input('gender'),
                ];

                $url = env('BASE_URL_VERIFY_USER') . 'api/v1/verify-demo';
                $token = env('VERIFY_USER_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
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
                    throw new \Exception('cURL Error: ' . curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] !== "norecord") {

                    $data = $response['message'];

                    $this->processResponseDataForNINDEMO($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
                    $ddesc = '';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }

                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Demo Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] === 'norecord') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }else if(isset($response['status']) && $response['status'] === 'caption'){

                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Caption: '.$response['message']],
                    ], 422);

                }else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function retrieveNIN(Request $request)
    {

        //  $request->validate(['nin' =>'required|numeric|digits:11']);

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN or Phone number is required.',
                'nin.numeric' => 'The NIN  or Phone number must be a numeric value.',
                'nin.digits' => 'The NIN  or Phone number must be exactly 11 digits.',
            ]
        );

        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '113')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                if ($request->route()->named('nin-phone')) {

                    $endpoint_part = '/nin/phone2';
                } else {
                    $endpoint_part = '/nin/v2';
                }

                $referenceNumber = Str::upper(Str::random(10));
                $endpoint = env('ENDPOINT') . $endpoint_part;
                $postdata = [
                    'value' => $request->input('nin'), //NIN Mondatory
                    'ref' => $referenceNumber,
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Authorization: ' . env('ACCESS_TOKEN') . '',
                    ]
                );
                $response = curl_exec($ch);
                curl_close($ch);

                //$data = $this->formatAndDecodeJson($response);
                $data = json_decode($response, true);

                if ($data['success'] == true && $data['data']['status'] == 'found') {

                    $this->processResponseData($data);

                    $balance = $wallet->balance - $ServiceFee;

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

                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    //Return Json response
                    return json_encode(['status' => $data['success'], 'data' => $data]);
                } else if ($data['success'] == true && $data['data']['status'] == 'not_found') {


                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
                    $ddesc = '';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }
                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format(
                            $ServiceFee,
                            2
                        ),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ' . $data['data']['reason']],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function retrieveNIN2(Request $request)
    {

        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '154')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $referenceNumber = Str::upper(Str::random(10));
                $endpoint = env('ENDPOINT') . '/nin/pulling';

                $postdata = [
                    'trkid' => $request->input('trackingId'),
                    'ref' => $referenceNumber,
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Authorization: ' . env('ACCESS_TOKEN') . '',
                    ]
                );


                $response = curl_exec($ch);

                curl_close($ch);

                $data = json_decode($response, true);

                if ($data['status'] == true && $data['message']['status'] == '1') {

                    $this->processResponseData2($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';

                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }

                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    //Return Json response
                    return json_encode(['status' => $data['status'], 'data' => $data]);
                } else if ($data['status'] == false) {

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';
                    $ddesc = '';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }
                    $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'NIN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format(
                            $ServiceFee,
                            2
                        ),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    return response()->json([
                        'status' => 'Invalid',
                        'errors' => ['Invalid Tracking Id.'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function regularSlip($nin_no)
    {
        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '114')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $ServiceFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $data = "123456123456789071234567890890";
            $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($data, (rand() % (strlen($data))), 1);
            }

            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $user = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'Regular NIN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            //Generate PDF
            $repObj = new NIN_PDF_Repository();
            $response = $repObj->regularPDF($nin_no);
            return  $response;
        }
    }


    public function standardSlip($nin_no)
    {


        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '115')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $ServiceFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $data = "123456123456789071234567890890";
            $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($data, (rand() % (strlen($data))), 1);
            }

            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $user = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'Standard NIN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            //Generate PDF
            $repObj = new NIN_PDF_Repository();
            $response = $repObj->standardPDF($nin_no);
            return  $response;
        }
    }

    public function premiumSlip($nin_no)
    {
        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '116')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $ServiceFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $data = "123456123456789071234567890890";
            $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($data, (rand() % (strlen($data))), 1);
            }

            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $user = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'Premium NIN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            //Generate PDF
            $repObj = new NIN_PDF_Repository();
            $response = $repObj->premiumPDF($nin_no);
            return  $response;
        }
    }

    public function basicSlip($nin_no)
    {
        //NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '172')->first();
        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;
        //Check if wallet is funded
        $wallet = Wallet::where('user_id',  $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $ServiceFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            Wallet::where('user_id',  $loginUserId)
                ->update(['balance' => $balance]);


            $referenceno = "";
            srand((float) microtime() * 1000000);
            $data = "123456123456789071234567890890";
            $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($data, (rand() % (strlen($data))), 1);
            }

            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $user = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'Basic NIN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            //Generate PDF
            $repObj = new NIN_PDF_Repository();
            $response = $repObj->basicPDF($nin_no);
            return  $response;
        }
    }

    private function formatAndDecodeJson($jsonString)
    {

        $replaceString = '||||statusCode||||200||||data||||message||||90';
        $replaceString2 = '[]}||||21||||';

        //Replace Json
        $cleanedString = str_replace($replaceString, '', $jsonString);
        $cleanedString = str_replace($replaceString2, '', $cleanedString);


        // Remove newline characters and excessive whitespace
        $formattedString = preg_replace('/\s+/', ' ', $cleanedString);

        // Fix potential issues with escaped quotes
        $formattedString = str_replace('\"', '"', $formattedString);

        // Trim leading and trailing whitespace
        $formattedString = trim($formattedString) . "}";

        //return $formattedString;

        // Decode the JSON string
        $jsonData = json_decode($formattedString, true);

        return $jsonData;
    }

    private function processResponseData($data)
    {


        $user = Verification::create([
            'idno' => $data['data']['idNumber'],
            'type' => 'NIN',
            'nin' => $data['data']['idNumber'],
            // 'trackingId' => $data['nin_data']['trackingId'],
            // 'title' => $data['nin_data']['title'],
            'first_name' => $data['data']['firstName'],
            'middle_name' => $data['data']['middleName'],
            'last_name' => $data['data']['lastName'],
            'phoneno' => $data['data']['mobile'],
            'email' => $data['data']['email'],
            'dob' => $data['data']['dateOfBirth'],
            'gender' => $data['data']['gender'] == 'm' || $data['data']['gender'] == 'Male' ? "Male" : "Female",
            'state' => $data['data']['state'],
            'lga' => $data['data']['lga'],
            'address' => $data['data']['addressLine'],
            'photo' => $data['data']['image'],
        ]);
    }
    public function processResponseData2($data)
    {

        $user = Verification::create([
            'idno' => $data['message']['nin'],
            'type' => 'NIN',
            'nin' => $data['message']['nin'],
            'trackingId' => $data['message']['trackingid'],
            'first_name' => $data['message']['firstname'],
            'middle_name' => $data['message']['middlename'],
            'last_name' => $data['message']['lastname'],
            'dob' => '1970-01-01',
            'gender' => $data['message']['gender'] == 'm' || $data['data']['gender'] == 'Male' ? 'Male' : 'Female',
            'state' => $data['message']['state'],
            'lga' => $data['message']['town'],
            'address' => $data['message']['address'],
            'photo' => $data['message']['face'],
        ]);
    }
    public function processResponseData3($data)
    {
        $user = Verification::create([
            'idno' => $data['data']['nin'],
            'type' => 'NIN',
            'nin' => $data['data']['nin'],
            'first_name' => $data['data']['firstName'],
            'middle_name' => $data['data']['middleName'],
            'last_name' => $data['data']['surname'],
            'dob' =>  $data['data']['birthDate'],
            'gender' => $data['data']['gender'],
            'phoneno' => $data['data']['telephoneNo'],
            'photo' => $data['data']['photo'],
        ]);
    }

    public function processResponseDataForNINDEMO($data)
    {

        try {
            Verification::create([
                'user_id' => auth()->user()->id,
                'idno' => $data['idNumber'],
                'type' => 'NIN',
                'nin' => $data['idNumber'],
                'trackingId' => $data['trackingId'],
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'],
                'last_name' => $data['lastName'],
                'phoneno' => $data['mobile'],
                'dob' => \Carbon\Carbon::createFromFormat('d-m-Y', $data['dateOfBirth'])->format('Y-m-d'),
                'gender' => $data['gender'] == 'm' || $data['gender'] == 'Male' ? 'Male' : 'Female',
                'state' => $data['self_origin_state'],
                'lga' => $data['self_origin_lga'],
                'town' => $data['self_origin_place'],
                'address' => $data['addressLine'],
                'photo' => $data['photo'],
                'signature' => $data['signature'],
                'residence_state' => $data['residence_state'],
                'residence_lga' => $data['residence_lga'],

            ]);
        } catch (\Exception $e) {

            Log::error('Verification creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create verification record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
