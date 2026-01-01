<?php

namespace App\Http\Controllers\Action;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{
    protected $loginUserId;

    // Constructor to initialize the property
    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    public function fetchBanks()
    {
        // Fetch bank codes from the database
        $banks = DB::table('banks')->select(['bank_code', 'bank_name', 'bank_url'])->get();

        // Return the bank codes as a JSON response
        return response()->json($banks);
    }

    public function verifyBankAccount(Request $request)
    {
        // Retrieve the query parameters
        $accountNumber = $request->query('acctno');
        $bankCode = $request->query('bankCode');

        try {
            $requestTime = (int) (microtime(true) * 1000);

            $noncestr = noncestrHelper::generateNonceStr();

            $data = [
                'requestTime' => $requestTime,
                'version' => env('VERSION'),
                'nonceStr' => $noncestr,
                'bankCode' => $bankCode,
                'bankAccNo' => $accountNumber,
            ];

            $signature = signatureHelper::generate_signature($data, config('keys.private'));

            $url = env('BASE_URL3').'api/v2/payment/merchant/payout/queryBankAccount';
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
            $responseData = json_decode($response, true);

            return $responseData;

        } catch (\Exception $e) {
            Log::error('Error in get Account Details: '.$e->getMessage());

        }

    }

    public function createOrUpdateBank(array $data)
    {
        // Check if a bank with the given bankCode already exists
        $bank = Bank::updateOrCreate(
            ['bank_code' => $data['bankCode']], // Search condition
            [   // Data to update or insert
                'bank_name' => $data['bankName'],
                'bank_url' => $data['bankUrl'],
                'bg_url' => $data['bgUrl'],
            ]
        );
    }

    public function pullBankCodes()
    {
        try {
            $requestTime = (int) (microtime(true) * 1000);

            $noncestr = noncestrHelper::generateNonceStr();

            $data = [
                'requestTime' => $requestTime,
                'version' => env('VERSION'),
                'nonceStr' => $noncestr,
                'merchantId' => env('MERCHANTID'),
                'businessType' => 0,
            ];

            $signature = signatureHelper::generate_signature($data, config('keys.private'));

            $url = env('BASE_URL3').'api/v2/general/merchant/queryBankList';
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

            Log::info('Api R|esponse: '.$response);

            // Decode the JSON response to an associative array
            $responseData = json_decode($response, true);

            // Handle cases where the response is invalid
            if (! is_array($responseData)) {
                throw new \Exception('Invalid JSON response from API');
            }

            // Check if the 'data' key exists and it's an array
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                // Loop through each bank in the data array
                foreach ($responseData['data'] as $bank) {
                    // Ensure that 'bankCode' and other keys exist before using them
                    if (isset($bank['bankCode'], $bank['bankName'], $bank['bankUrl'], $bank['bgUrl'])) {
                        // Call the function to create or update the bank record
                        $this->createOrUpdateBank([
                            'bankCode' => $bank['bankCode'],
                            'bankName' => $bank['bankName'],
                            'bankUrl' => $bank['bankUrl'],
                            'bgUrl' => $bank['bgUrl'],
                        ]);
                    } else {
                        // Handle the case where any expected key is missing
                        throw new \Exception('Missing data for bank: '.json_encode($bank));
                    }
                }
            } else {
                throw new \Exception('No data key or invalid format in response');
            }

            return response()->json(200);

        } catch (\Exception $e) {
            Log::error('Error in genBankCodes: '.$e->getMessage());
        }
    }
}
