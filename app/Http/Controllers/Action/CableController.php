<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CableController extends Controller
{
    /**
     * Show Cable TV Purchase Page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch Cable purchase history
        $history = Report::where('user_id', $user->id)
            ->where('type', 'cable')
            ->latest()
            ->paginate(10);

        return view('utilities.buy-cable', compact('wallet', 'history'));
    }

    /**
     * Fetch Variations (Plans) from DB or VTPass
     */
    public function getVariations(Request $request)
    {
        $request->validate(['service_id' => 'required|string']);
        $serviceId = $request->service_id;

        // 1. Try fetching from Database first
        $variations = DB::table('data_variations')
            ->where('service_id', $serviceId)
            ->select('variation_code as code', 'name', 'variation_amount as amount')
            ->get();

        if ($variations->isNotEmpty()) {
            return response()->json(['success' => true, 'variations' => $variations]);
        }

        // 2. If not in DB, fetch from API
        try {
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get(env('VARIATION_URL') . $serviceId);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['content']['variations'])) {
                    $variations = [];
                    foreach ($data['content']['variations'] as $v) {
                        // Prepare for response
                        $variations[] = [
                            'code'   => $v['variation_code'],
                            'name'   => $v['name'],
                            'amount' => $v['variation_amount'],
                        ];
                        
                        // Save to DB
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $v['variation_code'], 'service_id' => $serviceId],
                            [
                                'name'             => $v['name'],
                                'variation_amount' => $v['variation_amount'],
                                'fixed_price'      => $v['fixedPrice'] ?? 'Yes',
                                'updated_at'       => Carbon::now(),
                            ]
                        );
                    }
                    return response()->json(['success' => true, 'variations' => $variations]);
                }
            }
            return response()->json(['success' => false, 'message' => 'Failed to fetch plans.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching plans.']);
        }
    }

    /**
     * Verify Smartcard / IUC Number
     */
    public function verifyIuc(Request $request)
    {
        $request->validate([
            'service_id' => 'required|string',
            'billersCode' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://sandbox.vtpass.com/api') . '/merchant-verify', [
                'serviceID'   => $request->service_id,
                'billersCode' => $request->billersCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] == '000') {
                    $content = $data['content'];
                    
                    return response()->json([
                        'success'        => true,
                        'customer_name'  => $content['Customer_Name'] ?? 'Unknown',
                        'status'         => $content['Status'] ?? 'N/A',
                        'due_date'       => $content['Due_Date'] ?? 'N/A',
                        'customer_number'=> $content['Customer_Number'] ?? $request->billersCode,
                        'current_bouquet'=> $content['Current_Bouquet'] ?? 'N/A', // Some APIs return this
                        'renewal_amount' => $content['Renewal_Amount'] ?? 0, // Important for renewal
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Unable to verify IUC number.']);

        } catch (\Exception $e) {
            Log::error('Cable Verification Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed.']);
        }
    }

    /**
     * Purchase Cable Subscription
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'service_id'        => 'required|string',
            'billersCode'       => 'required|string',
            'subscription_type' => 'required|string|in:change,renew',
            'phone'             => 'required|numeric|digits:11',
            'amount'            => 'required|numeric',
            // variation_code is required if type is 'change'
            'variation_code'    => 'nullable|string',
        ]);

        $user = Auth::user();
        $requestId = RequestIdHelper::generateRequestId();
        $amount = $request->amount;

        // Lock and Check Wallet Balance, then pre-debit
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $amount) {
                $w = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$w || $w->balance < $amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }
                // Pre-debit the wallet
                $w->decrement('balance', $amount);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $isSuccessful = false;
        $result = null;
        $errorMessage = 'Subscription failed. Try again.';

        try {
            $payload = [
                'request_id'        => $requestId,
                'serviceID'         => $request->service_id,
                'billersCode'       => $request->billersCode,
                'subscription_type' => $request->subscription_type,
                'amount'            => $amount,
                'phone'             => $request->phone,
            ];

            if ($request->subscription_type === 'change') {
                if (!$request->variation_code) {
                    throw new \Exception('Please select a plan for bouquet change.');
                }
                $payload['variation_code'] = $request->variation_code;
            }

            // Call VTPass API
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if (!$isSuccessful) {
                    Log::error('Cable API Error', ['response' => $result]);
                    $errorMessage = 'Subscription failed. ' . ($result['response_description'] ?? 'Try again.');
                }
            } else {
                Log::error('Cable HTTP Error', ['body' => $response->body()]);
                $errorMessage = 'Service unavailable.';
            }

        } catch (\Exception $e) {
            Log::error('Cable Exception: ' . $e->getMessage());
            $errorMessage = $e->getMessage() ?: 'An error occurred.';
        }

        if ($isSuccessful) {
            $serviceName = strtoupper($request->service_id);
            $subType = ucfirst($request->subscription_type);
            $description = "{$serviceName} Subscription ({$subType}) - IUC: {$request->billersCode}";

            // Transaction Record
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $amount,
                'description'     => $description,
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'service_id'   => $request->service_id,
                    'billersCode'  => $request->billersCode,
                    'sub_type'     => $request->subscription_type,
                    'variation'    => $request->variation_code,
                    'api_response' => $result,
                ]),
                'performed_by' => $user->first_name . ' ' . $user->last_name,
                'approved_by'  => $user->id,
            ]);

            // Get current wallet balance for reporting
            $currentWalletBalance = Wallet::where('user_id', $user->id)->value('balance') ?? 0;

            // Report Record
            Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->billersCode,
                'network'      => $request->service_id,
                'ref'          => $requestId,
                'amount'       => $amount,
                'status'       => 'successful',
                'type'         => 'cable',
                'description'  => $description,
                'old_balance'  => $currentWalletBalance + $amount,
                'new_balance'  => $currentWalletBalance,
            ]);

            return redirect()->route('thankyou')->with([
                'success' => 'Cable subscription successful!',
                'ref'     => $requestId,
                'mobile'  => $request->billersCode,
                'amount'  => $amount,
                'token'   => 'Subscription Active',
                'network' => $serviceName
            ]);
        }

        // On failure, refund/reverse the wallet debit
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $amount) {
                $w = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if ($w) {
                    $w->increment('balance', $amount);
                }
            });
        } catch (\Exception $rollbackEx) {
            Log::error('Wallet refund failed: ' . $rollbackEx->getMessage());
        }

        return back()->with('error', $errorMessage);
    }
}
