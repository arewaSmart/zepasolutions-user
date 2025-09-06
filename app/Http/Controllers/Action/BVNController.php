<?php

namespace App\Http\Controllers\Action;

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
use GuzzleHttp\Exception\RequestException;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;

class BVNController extends Controller
{
    use ActiveUsers;
    use KycVerify;

    protected $loginUserId;
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->loginUserId = Auth::id();
        $this->walletService = $walletService;
    }

    public function show(Request $request)
    {

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $serviceCodes = ['101', '102', '103', '160'];
        $services = Services::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        $BVNFee = $services->get('101');
        $bvn_standard_fee = $services->get('102');
        $bvn_premium_fee = $services->get('103');
        $bvn_v2_fee = $services->get('160');

        $notificationsEnabled = Auth::user()->notification;

        switch (true) {
            case $request->route()->named('bvn'):
                $viewName = 'bvn-verify';
                break;

            case $request->route()->named('bvn2'):
                $viewName = 'bvn-verify2';
                break;
            default:
                $viewName = 'bvn-verify';
                break;
        }

        return view($viewName, compact(
            'notifications',
            'notifyCount',
            'BVNFee',
            'bvn_standard_fee',
            'bvn_premium_fee',
            'bvn_v2_fee',
            'notificationsEnabled'
        ));
    }

    public function bvnV2Retrieve(Request $request)
    {

        $request->validate(['bvn' => 'required|numeric|digits:11']);

        //BVN Services Fee
        $BVNFee = 0;
        $BVNFee = Services::where('service_code', '160')->first();
        $BVNFee = $BVNFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $BVNFee) {
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
                    'bvn' => $request->bvn,
                ];

                $signature = signatureHelper::generate_signature($data, config('keys.private2'));

                $url = env('Domain') . '/api/validator-service/open/bvn/inquire';
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

                    $this->processResponseDataV2($data);

                    $balance = $wallet->balance - $BVNFee;

                    $affected = Wallet::where('user_id', $this->loginUserId)
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
                        'service_type' => 'BVN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($BVNFee, 2),
                        'amount' => $BVNFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "verification_v2");

                    //Return Json response
                    return json_encode(['status' => 'success', 'data' => $data]);
                } else if ($data['respCode'] == 99120020) {

                    $balance = $wallet->balance - $BVNFee;

                    Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $gen = '123456123456789071234567890890';
                    $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
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
                            $BVNFee,
                            2
                        ),
                        'amount' => $BVNFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "verification_v2");

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( BVN does not exist)'],
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
    public function retrieveBVN(Request $request)
    {

        $request->validate(['bvn' => 'required|numeric|digits:11']);

        //BVN Services Fee
        $BVNFee = 0;
        $BVNFee = Services::where('service_code', '101')->first();
        $BVNFee = $BVNFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $BVNFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {


            try {

                $referenceNumber = random_int(1000000000, 9999999999);
                $postdata = array(
                    'value' => $request->input('bvn'),
                    'ref' => $referenceNumber
                );

                $endpoint_part = "/bvn2/verify";
                $endpoint = env('ENDPOINT') . $endpoint_part;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        "Content-Type: application/json",
                        "Authorization: " . env('ACCESS_TOKEN') . "",
                    ]
                );
                $response = curl_exec($ch);
                curl_close($ch);

                $data = $this->formatAndDecodeJson($response);

                if ($data['success'] == true && $data['data']['status'] == 'found') {

                    $this->processResponseData($data);

                    $balance = $wallet->balance - $BVNFee;

                    $affected = Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $referenceno = "";
                    srand((float) microtime() * 1000000);
                    $gen = "123456123456789071234567890890";
                    $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
                    $ddesc = "";
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }

                    $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' =>  $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'BVN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($BVNFee, 2),
                        'amount' => $BVNFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    //Return Json response
                    return json_encode(['status' => $data['success'], 'data' => $data]);
                } else {
                    $referenceno = "";
                    srand((float) microtime() * 1000000);
                    $gen = "123456123456789071234567890890";
                    $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
                    $ddesc = "";
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
                    }

                    $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' =>  $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'BVN Verification',
                        'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($BVNFee, 2),
                        'amount' => $BVNFee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ' . $data['data']['reason']]
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

    public function premiumBVN($bvnno)
    {

        //BVN Services Fee
        $BVNFee = 0;
        $BVNFee = Services::where('service_code', '103')->first();
        $BVNFee = $BVNFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $BVNFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $BVNFee;



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
                'service_type' => 'Premium BVN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($BVNFee, 2),
                'amount' => $BVNFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('PremiumBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    "message" => "Error",
                    "errors" => array("Not Found" => "Verification record not found !")
                ], 422);
            }
        }
    }

    public function standardBVN($bvnno)
    {

        //BVN Services Fee
        $BVNFee = 0;
        $BVNFee = Services::where('service_code', '102')->first();
        $BVNFee = $BVNFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  < $BVNFee) {
            return response()->json([
                "message" => "Error",
                "errors" => array("Wallet Error" => "Sorry Wallet Not Sufficient for Transaction !")
            ], 422);
        } else {
            $balance = $wallet->balance - $BVNFee;



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
                'service_type' => 'Standard BVN Slip',
                'service_description' => 'Wallet debitted with a service fee of ₦' . number_format($BVNFee, 2),
                'amount' => $BVNFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "slip_download");

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('freeBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    "message" => "Error",
                    "errors" => array("Not Found" => "Verification record not found !")
                ], 422);
            }
        }
    }

    private function processResponseData($data)
    {

        // //Update DB with Verification Details
        $user = Verification::create(
            [
                'idno' => $data['data']['idNumber'],
                'type' => 'BVN',
                'nin' => $data['data']['nin'],
                'first_name' => $data['data']['firstName'],
                'middle_name' => $data['data']['middleName'],
                'last_name' => $data['data']['lastName'],
                'phoneno' => $data['data']['mobile'],
                // 'email' => $data['data']['email'],
                'dob' => $data['data']['dateOfBirth'],
                'gender' => $data['data']['gender'],
                'enrollment_branch' => $data['data']['enrollmentBranch'],
                'enrollment_bank' => $data['data']['enrollmentInstitution'],
                // 'registration_date' => '',
                // 'address' =>'',
                'photo' => $data['data']['image'],
            ]
        );
    }
    private function formatAndDecodeJson($jsonString)
    {
        $replaceString = '||||statusCode||||200||||data||||message||||0';
        $replaceString2 = '[]}||||35||||';

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

    function formatDate($date)
    {
        // Check if date is already in the format 'Y-m-d'
        if (Carbon::hasFormat($date, 'Y-m-d')) {
            return $date;
        }

        // Check and convert if date is in 'd-M-Y' format
        try {
            return Carbon::createFromFormat('d-M-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Handle invalid date format if necessary
            return null;
        }
    }

    private function processResponseDataV2($data)
    {

        //Update DB with Verification Details
        $user = Verification::create(
            [
                'idno' => $data['data']['bvn'],
                'type' => 'BVN',
                'nin' => '',
                'first_name' => $data['data']['firstName'],
                'middle_name' => $data['data']['middleName'],
                'last_name' => $data['data']['lastName'],
                'phoneno' => $data['data']['phoneNumber'],
                'dob' => $data['data']['birthday'],
                'gender' => $data['data']['gender'],
                'photo' => $data['data']['photo'],
            ]
        );
    }
}
