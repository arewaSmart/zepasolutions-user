<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ServiceField;
use App\Models\AgentService;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ManualSearchController extends Controller
{
    /**
     * Display phone number submission page with submission history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch all valid submissions (number not null/empty)
          $query = AgentService::where('user_id', $user->id)
        ->where('service_type', 'bvn_search');

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('number', 'like', '%' . $request->search . '%');
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Custom ordering: pending → processing → others
        $query->orderByRaw("
            CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'processing' THEN 2
                ELSE 3
            END
        ")->orderByDesc('submission_date');

        // Paginate results
        $crmSubmissions = $query->paginate(5)->withQueryString();

        // Fetch active phone search service
        $phoneService = Service::where('name', 'BVN SEARCH')
            ->where('is_active', true)
            ->first();

        // Load active fields for this service
        $serviceFields = $phoneService
            ? ServiceField::where('service_id', $phoneService->id)
                ->where('is_active', true)
                ->get()
            : collect();

        return view('bvn.phone-search', [
            'serviceFields'  => $serviceFields,
            'crmSubmissions' => $crmSubmissions,
            'services'       => Service::where('is_active', true)->get(),
            'bvnService'     => $phoneService,
            'wallet'         => $wallet,
        ]);
    }

    /**
     * Handle phone number submission and charge user based on selected service and role.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'service_field_id' => 'required|exists:service_fields,id',
            'number' => 'required|string|size:11|regex:/^[0-9]{11}$/',
        ]);

        $serviceField = ServiceField::with('service')->findOrFail($validated['service_field_id']);
        $serviceName = $serviceField->service->name ?? 'Unknown Service';
        $servicePrice = $serviceField->getPriceForUserType($user->role);

        if ($servicePrice === null) {
            return back()->with([
                'status' => 'error',
                'message' => 'Service price not configured for your user role.',
            ])->withInput();
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
            return back()->with([
                'status' => 'error',
                'message' => 'Your wallet is inactive. Please contact support.',
            ])->withInput();
        }

        if ($wallet->balance < $servicePrice) {
            return back()->with([
                'status' => 'error',
                'message' => 'Insufficient wallet balance. You need NGN ' .
                    number_format($servicePrice - $wallet->balance, 2) . ' more.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // Lock wallet inside the transaction to prevent concurrent deductions
            $lockedWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($lockedWallet->status !== 'active') {
                throw new \Exception('Your wallet is inactive. Please contact support.');
            }

            if ($lockedWallet->balance < $servicePrice) {
                throw new \Exception('Insufficient wallet balance. You need NGN ' .
                    number_format($servicePrice - $lockedWallet->balance, 2) . ' more.');
            }

            // Pre-debit wallet before API call
            $lockedWallet->decrement('balance', $servicePrice);

            $transactionRef = 'P1' . date('is') . strtoupper(Str::random(5));
            $performedBy = trim($user->first_name . ' ' . $user->last_name);

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "{$serviceName} for {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => 'phone_search',
                    'service_name' => $serviceName,
                    'service_field' => $serviceField->field_name,
                    'field_code' => $serviceField->field_code,
                    'number' => $validated['number'],
                    'user_role' => $user->role,
                ],
            ]);


            // 4. API Submission to Digital Verify Sub
            $apiKey = env('AREWA_API_TOKEN');
            $apiBaseUrl = env('AREWA_BASE_URL', 'https://api.arewasmart.com.ng/api/v1');
            $apiUrl = rtrim($apiBaseUrl, '/') . '/bvn/phone-search';

            $payload = [
                'field_code' => $serviceField->field_code,
                'phone_number' => $validated['number'],
            ];

            try {
                $response = Http::withToken($apiKey)
                    ->withoutVerifying()
                    ->acceptJson()
                    ->post($apiUrl, $payload);

                
                $data = $response->json();

                if (!$response->successful() || !($data['success'] ?? false)) {
                    throw new \Exception('API Submission Failed: ' . ($data['message'] ?? 'Unknown Provider Error'));
                }
            } catch (\Exception $e) {
                Log::error('Arewa BVN Search API Error: ' . $e->getMessage());
                throw new \Exception('Connection Error: Unable to reach service provider. ' . $e->getMessage());
            }

            // Record submission
            AgentService::create([
                'reference' => $data['data']['reference'] ?? $transactionRef,
                'user_id' => $user->id,
                'service_field_id' => $serviceField->id,
                'service_id' => $serviceField->service_id,
                'field_code' => $serviceField->field_code,
                'field_name' => $serviceField->field_name,
                'amount' => $servicePrice,
                'service_name' => $serviceName,
                'number' => $validated['number'],
                'transaction_id' => $transaction->id,
                'performed_by' => $performedBy,
                'submission_date' => now(),
                'status' => 'processing', // Set to processing after successful API submission
                'service_type' => 'bvn_search',
                'comment' => $data['message'] ?? 'Submitted to Arewa API',
            ]);

            DB::commit();

            return redirect()->route('phone.search.index')->with([
                'status' => 'success',
                'message' => 'BVN Search request submitted successfully. Ref: ' . ($data['data']['reference'] ?? $transactionRef) .
                    '. Charged NGN ' . number_format($servicePrice, 2),
            ]);


        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with([
                'status' => 'error',
                'message' => 'Submission failed: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Check status of a BVN Search request for the user.
     */
    public function checkStatus($id)
    {
        try {
            $enrollment = AgentService::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('service_type', 'bvn_search')
                ->firstOrFail();
            
            $apiToken = env('AREWA_API_TOKEN');
            $baseUrl = env('AREWA_BASE_URL', 'https://api.arewasmart.com.ng/api/v1');
            $endpoint = rtrim($baseUrl, '/') . '/bvn/phone-search';

            $response = Http::withToken($apiToken)
                ->withoutVerifying()
                ->acceptJson()
                ->get($endpoint, [
                    'reference' => $enrollment->reference,
                ]);

            if ($response->successful()) {
                $apiResponse = $response->json();
                $cleanResponse = $this->cleanApiResponse($apiResponse);
                
                $updateData = [
                    'comment' => $cleanResponse,
                ];

                $data = $apiResponse['data'] ?? $apiResponse;

                if (isset($data['status'])) {
                    $updateData['status'] = $this->normalizeStatus($data['status']);
                }
                
                if (isset($data['bvn'])) {
                    $updateData['bvn'] = $data['bvn'];
                }

                $isFailingNow = isset($updateData['status']) && $updateData['status'] === 'failed' && $enrollment->status !== 'failed';

                if ($isFailingNow) {
                    DB::beginTransaction();
                    try {
                        $enrollment->update($updateData);

                        $wallet = Wallet::where('user_id', $enrollment->user_id)->lockForUpdate()->first();
                        if ($wallet) {
                            $wallet->increment('balance', $enrollment->amount);

                            Transaction::create([
                                'transaction_ref' => 'REF_' . $enrollment->reference,
                                'user_id'         => $enrollment->user_id,
                                'amount'          => $enrollment->amount,
                                'performed_by'    => 'System Auto-Refund',
                                'description'     => "Refund for failed BVN Phone Search Request",
                                'type'            => 'credit',
                                'status'          => 'completed',
                                'metadata'        => [
                                    'original_reference' => $enrollment->reference,
                                    'api_reason'         => $updateData['comment'] ?? 'Failed submission',
                                ],
                            ]);
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('BVN Phone Search Auto Refund Error', ['error' => $e->getMessage(), 'submission_id' => $enrollment->id]);
                        throw $e;
                    }
                } else {
                    $enrollment->update($updateData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Status updated: ' . ucfirst($enrollment->status),
                    'data' => [
                        'status' => $enrollment->status,
                        'comment' => $enrollment->comment,
                        'bvn' => $enrollment->bvn,
                    ]
                ]);
            }

            $lastError = $response->json('message') ?? $response->json('error') ?? 'Provider API error.';

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $lastError,
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function cleanApiResponse($response): string
    {
        if (is_array($response)) {
            $data = $response['data'] ?? $response;
            if (isset($data['comment']) && is_string($data['comment'])) return $data['comment'];
            if (isset($response['message']) && is_string($response['message'])) return $response['message'];

            $toExclude = ['status', 'success', 'bvn', 'response', 'message', 'comment', 'reference', 'phone_number', 'field_code'];
            $toKeep = array_diff_key($data, array_flip($toExclude));

            $parts = [];
            foreach ($toKeep as $key => $value) {
                if (!is_scalar($value) || strlen((string)$value) > 255) continue;
                $label = ucfirst(str_replace(['_', '-'], ' ', $key));
                $parts[] = $label . ': ' . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value);
            }
            return !empty($parts) ? implode(', ', $parts) : 'Processed';
        }
        return (string) $response;
    }

    private function normalizeStatus($status): string
    {
        $s = strtolower(trim((string) $status));
        return match ($s) {
            'successful', 'success', 'resolved', 'approved', 'completed' => 'successful',
            'processing', 'in_progress', 'in-progress', 'submitted', 'new', 'pending' => 'processing',
            'failed', 'rejected', 'error', 'declined', 'invalid', 'no record' => 'failed',
            'query', 'queried' => 'query',
            default => 'pending',
        };
    }

    /**
     * Fetch dynamic service field price based on user role.

     */
    public function getFieldPrice(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:service_fields,id',
        ]);

        $user = Auth::user();
        $field = ServiceField::findOrFail($request->field_id);
        $price = $field->getPriceForUserType($user->role);

        return response()->json([
            'success' => true,
            'price' => $price,
            'formatted_price' => 'NGN ' . number_format($price, 2),
            'field_name' => $field->field_name,
            'base_price' => $field->base_price,
        ]);
    }
}
