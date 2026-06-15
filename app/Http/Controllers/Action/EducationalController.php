<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EducationalController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Educational Pin Services & Price Lists
     */
    public function pin(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Load pin variations
        $pins = DB::table('data_variations')->whereIn('service_id', ['waec', 'waec-registration'])->get();

        // Fetch purchase history
        $history = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'education')
            ->latest()
            ->paginate(10);

        return view('utilities.buy-educational-pin')->with(compact('pins', 'wallet', 'history'));
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Unauthorized']);
        }

        $userId = $user->id;
        $rateLimitKey = 'pin-attempts:'.$userId;

        // Check if the user has reached the limit
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $secondsUntilUnlock = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'valid' => false,
                'message' => 'Too many failed attempts. Please try again after '.gmdate('i:s', $secondsUntilUnlock).' minutes.',
            ]);
        }

        if (Hash::check($request->pin, $user->pin)) {
            // Clear the rate limiter on success
            \Illuminate\Support\Facades\RateLimiter::clear($rateLimitKey);
            return response()->json(['valid' => true]);
        } else {
            // Increment the rate limiter on failure
            \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 900); // Lockout for 15 minutes
            return response()->json([
                'valid' => false,
                'message' => 'Incorrect PIN. Please try again.',
            ]);
        }
    }

    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    public function getVariation(Request $request)
    {
        try {
            // Determine serviceID based on type
            $type = $request->type;
            $url = env('VARIATION_URL') . $type;

            // Special handling for JAMB if needed, but usually VTPass uses 'jamb' as serviceID for variations too
            // If type is 'jamb', URL is .../service-variations?serviceID=jamb

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['content']['variations'])) {
                    $serviceName = $data['content']['ServiceName'];
                    $serviceId = $data['content']['serviceID'];
                    $convenienceFee = $data['content']['convinience_fee'] ?? '0%';

                    foreach ($data['content']['variations'] as $variation) {
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $variation['variation_code']],
                            [
                                'service_name'     => $serviceName,
                                'service_id'       => $serviceId,
                                'convenience_fee'  => $convenienceFee,
                                'name'             => $variation['name'],
                                'variation_amount' => $variation['variation_amount'],
                                'fixed_price'      => $variation['fixedPrice'],
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]
                        );
                    }

                    return response()->json(['success' => true, 'message' => 'Variation list updated successfully.']);
                }
            }

            Log::error('VTpass Variation Fetch Failed', ['response' => $response->json()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch variations.']);
        } catch (\Exception $e) {
            Log::error('VTpass Variation Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }

    /**
     * Buy Educational Pin (WAEC / WAEC Registration)
     */
    public function buypin(Request $request)
    {
        $request->validate([
            'service'  => ['required', 'string', 'in:waec-registration,waec'],
            'type'     => ['required', 'string'],
            'mobileno' => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $requestId = RequestIdHelper::generateRequestId();

        // Get the selected variation details
        $variation = DB::table('data_variations')->where('variation_code', $request->type)->first();

        if (!$variation) {
            return back()->with('error', 'Invalid educational pin type selected.');
        }

        $fee = $variation->variation_amount;
        $description = $variation->name ?? 'Educational Pin';

        // Lock and Check Wallet Balance, then pre-debit
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fee) {
                $w = Wallet::where('user_id', $this->loginUserId)->lockForUpdate()->first();
                if (!$w || $w->balance < $fee) {
                    throw new \Exception('Insufficient wallet balance for this transaction.');
                }
                // Pre-debit the wallet
                $w->decrement('balance', $fee);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $isSuccessful = false;
        $result = null;
        $errorMessage = 'Something went wrong. Please try again.';

        try {
            // Call VTpass API
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $request->service,
                'billersCode'    => '0123456789', // Dummy biller code for WAEC/Result Checker
                'variation_code' => $request->type,
                'phone'          => $request->mobileno,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if (!$isSuccessful) {
                    Log::error('VTpass Educational Pin API Error', ['response' => $result]);
                    $errorMessage = 'Purchase failed. ' . ($result['response_description'] ?? 'Please try again later.');
                }
            } else {
                Log::error('VTpass Educational Pin HTTP Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                $errorMessage = 'Service temporarily unavailable. Try again later.';
            }
        } catch (\Exception $e) {
            Log::error('Educational Pin Purchase Exception', ['error' => $e->getMessage()]);
            $errorMessage = $e->getMessage() ?: 'Something went wrong. Please try again.';
        }

        if ($isSuccessful) {
            // Extract Purchased Code (PIN)
            // VTpass usually returns it in 'purchased_code' or inside 'cards' array
            $purchasedCode = $result['purchased_code'] ?? null;
            
            $pin = null;
            $serial = null;
            if (isset($result['cards']) && is_array($result['cards']) && count($result['cards']) > 0) {
                 $pin = $result['cards'][0]['Pin'] ?? null;
                 $serial = $result['cards'][0]['Serial'] ?? null;
            }
            
            if (!$pin && $purchasedCode) {
                $pin = $purchasedCode;
            }
            
            // Fallback if code is not found but transaction is successful
            $finalToken = $pin ?? 'Check Transaction History';

            $payer_name = $user->first_name . ' ' . $user->last_name;
            $transDescription = "Educational pin purchase ({$description}) - PIN: {$finalToken}";

            // Save transaction record
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $this->loginUserId,
                'amount'          => $fee,
                'description'     => $transDescription,
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'         => json_encode([
                    'phone'          => $request->mobileno,
                    'service'        => $request->service,
                    'purchased_code' => $finalToken,
                    'pin'            => $pin,
                    'serial'         => $serial,
                    'payer_name'     => $payer_name,
                    'payer_email'    => $user->email,
                    'payer_phone'    => $user->phone_number,
                    'gateway'        => 'Wallet',
                    'api_response'   => $result,
                ]),
                'performed_by' => $payer_name,
                'approved_by'  => $this->loginUserId,
            ]);

            // Get current balance
            $currentBalance = Wallet::where('user_id', $this->loginUserId)->value('balance') ?? 0;

            // Create Report
            \App\Models\Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->mobileno,
                'network'      => $request->service, // e.g. waec
                'ref'          => $requestId,
                'amount'       => $fee,
                'status'       => 'successful',
                'type'         => 'education',
                'description'  => $transDescription,
                'old_balance'  => $currentBalance + $fee,
                'new_balance'  => $currentBalance,
            ]);

            return redirect()->route('thankyou')->with([
                'success' => 'Educational pin purchase successful!',
                'ref'     => $requestId,
                'mobile'  => $request->mobileno,
                'amount'  => $fee,
                'token'   => $finalToken, // Pass the PIN as 'token' for thankyou page
                'pin'     => $pin,
                'serial'  => $serial,
                'network' => strtoupper($request->service) // Display name
            ]);
        }

        // On failure, refund
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fee) {
                $w = Wallet::where('user_id', $this->loginUserId)->lockForUpdate()->first();
                if ($w) {
                    $w->increment('balance', $fee);
                }
            });
        } catch (\Exception $rollbackEx) {
            Log::error('Wallet refund failed: ' . $rollbackEx->getMessage());
        }

        return back()->with('error', $errorMessage);
    }

    /**
     * Show JAMB Purchase Page
     */
    public function jamb(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch JAMB purchase history
        $history = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'jamb')
            ->latest()
            ->paginate(10);

        // Fetch JAMB variations
        $variations = DB::table('data_variations')->where('service_id', 'jamb')->get();

        return view('utilities.buy-jamb', compact('wallet', 'history', 'variations'));
    }

    /**
     * Verify JAMB Profile ID
     */
    public function verifyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
        ]);

        try {
            // Map the selected service to the VTPass Service ID
            // Usually 'jamb' is the serviceID for both UTME and DE
            $vtpassServiceId = 'jamb'; 

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://sandbox.vtpass.com/api') . '/merchant-verify', [
                'serviceID'   => $vtpassServiceId,
                'billersCode' => $request->profile_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] == '000') {
                    $customerName = $data['content']['Customer_Name'] ?? 'Unknown';
                    
                    // Fetch price from DB
                    // The frontend sends 'jamb' or 'jamb-de' as the 'service' (which acts as variation code here)
                    // We look up the 'data_variations' table using this code.
                    // If not found, we might default to a known price or error.
                    
                    $variationCode = $request->service; // 'jamb' or 'jamb-de'
                    
                    // Try to find by variation_code directly
                    $variation = DB::table('data_variations')->where('variation_code', $variationCode)->first();
                    
                    // If not found, try to find by service_id 'jamb' and name like... (fallback)
                    if (!$variation) {
                         // Fallback: if user sent 'jamb', look for 'utme' maybe? 
                         // For now, let's assume the DB is seeded with 'jamb' and 'jamb-de' as variation_codes.
                         // If not, we return 0 and user can't buy.
                    }

                    $amount = $variation ? $variation->variation_amount : 0;

                    return response()->json([
                        'success' => true, 
                        'customer_name' => $customerName,
                        'amount' => $amount
                    ]);
                }
            }
            
            return response()->json(['success' => false, 'message' => 'Invalid Profile ID or Service unavailable.']);

        } catch (\Exception $e) {
            Log::error('JAMB Verification Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed.']);
        }
    }

    /**
     * Buy JAMB PIN
     */
    public function buyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
            'mobileno'   => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        $requestId = RequestIdHelper::generateRequestId();

        // Get Price
        $variation = DB::table('data_variations')->where('variation_code', $request->service)->first();
        if (!$variation) {
            return back()->with('error', 'Invalid JAMB service selected.');
        }

        $fee = $variation->variation_amount;
        $description = $variation->name ?? 'JAMB PIN';

        // Lock and Check Wallet Balance, then pre-debit
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fee) {
                $w = Wallet::where('user_id', $this->loginUserId)->lockForUpdate()->first();
                if (!$w || $w->balance < $fee) {
                    throw new \Exception('Insufficient wallet balance.');
                }
                // Pre-debit the wallet
                $w->decrement('balance', $fee);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $isSuccessful = false;
        $result = null;
        $errorMessage = 'An error occurred.';

        try {
            $apiServiceId = 'jamb'; // Always 'jamb' for VTPass JAMB services

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $apiServiceId,
                'billersCode'    => $request->profile_id,
                'variation_code' => $request->service, // variation_code
                'phone'          => $request->mobileno,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if (!$isSuccessful) {
                    Log::error('JAMB API Error', ['response' => $result]);
                    $errorMessage = 'Purchase failed. ' . ($result['response_description'] ?? 'Try again.');
                }
            } else {
                Log::error('JAMB HTTP Error', ['body' => $response->body()]);
                $errorMessage = 'Service unavailable.';
            }

        } catch (\Exception $e) {
            Log::error('JAMB Exception: ' . $e->getMessage());
            $errorMessage = $e->getMessage() ?: 'An error occurred.';
        }

        if ($isSuccessful) {
            // Extract PIN
            $purchasedCode = $result['purchased_code'] ?? null;
            
            $pin = null;
            $serial = null;
            if (isset($result['cards']) && is_array($result['cards']) && count($result['cards']) > 0) {
                 $pin = $result['cards'][0]['Pin'] ?? null;
                 $serial = $result['cards'][0]['Serial'] ?? null;
            }
            
            if (!$pin && $purchasedCode) {
                $pin = $purchasedCode;
            }
            
            $finalToken = $pin ?? 'Check History';

            $payer_name = $user->first_name . ' ' . $user->last_name;
            $transDescription = "{$description} Purchase - Profile: {$request->profile_id} - PIN: {$finalToken}";

            // Transaction
            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $this->loginUserId,
                'amount'          => $fee,
                'description'     => $transDescription,
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'profile_id'     => $request->profile_id,
                    'purchased_code' => $finalToken,
                    'pin'            => $pin,
                    'serial'         => $serial,
                    'api_response'   => $result,
                ]),
                'performed_by' => $payer_name,
                'approved_by'  => $this->loginUserId,
            ]);

            // Get current wallet balance
            $currentWalletBalance = Wallet::where('user_id', $this->loginUserId)->value('balance') ?? 0;

            // Report
            \App\Models\Report::create([
                'user_id'      => $user->id,
                'phone_number' => $request->mobileno,
                'network'      => $request->service, // jamb or jamb-de
                'ref'          => $requestId,
                'amount'       => $fee,
                'status'       => 'successful',
                'type'         => 'jamb',
                'description'  => $transDescription,
                'old_balance'  => $currentWalletBalance + $fee,
                'new_balance'  => $currentWalletBalance,
            ]);

            return redirect()->route('thankyou')->with([
                'success' => 'JAMB PIN purchase successful!',
                'ref'     => $requestId,
                'mobile'  => $request->mobileno,
                'amount'  => $fee,
                'token'   => $finalToken,
                'pin'     => $pin,
                'serial'  => $serial,
                'network' => strtoupper($description)
            ]);
        }

        // On failure, refund
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fee) {
                $w = Wallet::where('user_id', $this->loginUserId)->lockForUpdate()->first();
                if ($w) {
                    $w->increment('balance', $fee);
                }
            });
        } catch (\Exception $rollbackEx) {
            Log::error('Wallet refund failed: ' . $rollbackEx->getMessage());
        }

        return back()->with('error', $errorMessage);
    }
}


