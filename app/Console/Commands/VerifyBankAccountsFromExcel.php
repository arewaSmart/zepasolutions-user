<?php

namespace App\Console\Commands;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Models\ExcelUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyBankAccountsFromExcel extends Command
{
    protected $signature = 'verify:bank-accounts';

    protected $description = 'Verify bank accounts from excel_uploads where status is 0';

    public function handle()
    {
        $pendingRecords = ExcelUpload::where('status', '0')->where('beneficiary_bankcode', '000014')->get();

        foreach ($pendingRecords as $record) {
            try {
                $accountNumber = $record->beneficiary_account;
                $bankCode = $record->beneficiary_bankcode;

                $verifiedName = $this->verifyBankAccount($accountNumber, $bankCode);

                if ($verifiedName) {
                    $record->new_account_name = $verifiedName;
                    $record->status = '1';
                    $record->save();

                    $this->info("Verified and updated: {$accountNumber}");
                } else {
                    $this->warn("Verification failed for: {$accountNumber}");
                }
            } catch (\Exception $e) {
                Log::error('Error verifying account: '.$e->getMessage());
            }
        }
    }

    private function verifyBankAccount($accountNumber, $bankCode)
    {
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL Error: '.curl_error($ch));
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        // ✅ Log full response for debugging
        Log::info('Bank verification API response', [
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'response' => $responseData,
        ]);

        // ✅ Adjusted response validation
        if (
            isset($responseData['respCode']) &&
            $responseData['respCode'] === '00000000' &&
            isset($responseData['data']['accountName'])
        ) {
            return $responseData['data']['accountName'];
        }

        return null;
    }
}
