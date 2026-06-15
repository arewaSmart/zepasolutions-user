<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NinValidationController extends Controller
{
    public function index(Request $request)
    {
        $validationService = Service::where('name', 'Validation')->first();
        if (!$validationService) {
            \App\Helpers\ServiceManager::getServiceWithFields('Validation', [
                ['name' => 'NIN Validation', 'code' => '015', 'price' => 100],
            ]);
            $validationService = Service::where('name', 'Validation')->first();
        }
        $validationFields = $validationService ? $validationService->fields : collect();

        $services = collect();
        $user = Auth::user();
        $role = $user->role ?? 'user';
        
        foreach ($validationFields as $field) {
            $price = $field->getPriceForUserType($role);
            $services->push([
                'id' => $field->id,
                'name' => $field->field_name,
                'price' => $price,
                'type' => 'validation',
                'service_id' => $field->service_id
            ]);
        }
        
        $wallet = Wallet::where('user_id', Auth::id())->first();
        
        $query = AgentService::where('user_id', Auth::id())
            ->where('service_type', 'NIN_VALIDATION'); // Specific to Validation

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('nin', 'like', "%{$searchTerm}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderByRaw("
          CASE status 
        WHEN 'pending' THEN 1 
        WHEN 'processing' THEN 2 
        WHEN 'successful' THEN 3 
        WHEN 'failed' THEN 4 
        WHEN 'resolved' THEN 5 
        WHEN 'rejected' THEN 6 
        ELSE 7 
            END
        ")->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('nin.validation', compact('services', 'wallet', 'submissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_field' => 'required',
            'nin' => 'required|digits:11',
        ]);

        $fieldId = $request->service_field;
        $serviceField = ServiceField::with('service')->findOrFail($fieldId);
        
        // Ensure this field belongs to the Validation service category
        if (!$serviceField->service || $serviceField->service->name !== 'Validation') {
            return back()->with('error', 'Invalid service selection.');
        }

        $user = Auth::user();
        $role = $user->role ?? 'user';
        
        $servicePrice = $serviceField->getPriceForUserType($role);

        // Preliminary check to avoid reaching the API if the balance is low
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet || $wallet->balance < $servicePrice) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $apiKey = env('AREWA_API_TOKEN');
        $apiBaseUrl = env('AREWA_BASE_URL');
        $apiUrl = rtrim($apiBaseUrl, '/') . '/nin/validation';

        $payload = [
            'description' => $request->description ?? "My Reference",
            'nin' => $request->nin,
            'field_code' => '015', // Code for Validation
        ];

        try {
            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->acceptJson()
                ->post($apiUrl, $payload);
            
            $data = $response->json();

            if (!$response->successful() || (isset($data['status']) && $data['status'] == 'error')) {
                return back()->with('error', 'API Submission Failed: ' . ($data['message'] ?? 'Unknown Error'));
            }
        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
            return back()->with('error', 'Connection Error: Unable to reach service provider.');
        }

        DB::beginTransaction();

        try {
            // Re-fetch wallet with a lock to prevent race conditions during deduction
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            
            if (!$wallet || $wallet->balance < $servicePrice) {
                throw new \Exception('Insufficient wallet balance at the time of processing.');
            }

            $wallet->decrement('balance', $servicePrice);

            $transactionRef = 'TRX-' . strtoupper(Str::random(10));
            $performedBy = $user->first_name . ' ' . $user->last_name;

            $cleanResponse = $this->cleanApiResponse($data);

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $servicePrice,
                'description' => "NIN Validation for {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $serviceField->service->name,
                    'service_field' => $serviceField->field_name,
                    'nin' => $request->nin,
                ],
            ]);

            $apiReference = $data['data']['reference'] ?? $data['reference'] ?? ('REF-' . strtoupper(Str::random(10)));
            $status = $this->normalizeStatus($data['status'] ?? 'processing');

            AgentService::create([
                'reference' => $apiReference,
                'user_id' => $user->id,
                'service_id' => $serviceField->service_id,
                'service_field_id' => $serviceField->id,
                'field_code' => $serviceField->field_code,
                'transaction_id' => $transaction->id,
                'service_type' => 'NIN_VALIDATION',
                'nin' => $request->nin,
                'amount' => $servicePrice,
                'status' => $status,
                'submission_date' => now(),
                'service_field_name' => $serviceField->field_name,
                'description' => $request->description ?? $serviceField->field_name,
                'comment' => $cleanResponse,
                'performed_by' => $performedBy,
            ]);

            DB::commit();
            return back()->with('success', 'NIN Validation Request submitted successfully. Status: ' . $status);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Error: ' . $e->getMessage());
            return back()->with('error', 'System Error: Failed to record transaction. Please contact support.');
        }
    }

    public function checkStatus(Request $request, $id = null)
    {
        try {
            if ($id) {
                $agentService = AgentService::findOrFail($id);
            } else {
                $request->validate([
                    'nin' => 'required|string',
                ]);
                $agentService = AgentService::where('nin', $request->nin)
                    ->orderBy('created_at', 'desc')
                    ->firstOrFail();
            }

            $apiKey = env('AREWA_API_TOKEN');
            $apiBaseUrl = env('AREWA_BASE_URL');
            $url = rtrim($apiBaseUrl, '/') . '/nin/validation';
            
            $payload = [
                'field_code' => $agentService->field_code ?? '015',
                'description' => $agentService->description ?? "Status Check",
            ];

            // If the reference is not a locally generated dummy (i.e. starts with REF-), prioritize it
            if ($agentService->reference && !str_starts_with($agentService->reference, 'REF-')) {
                $payload['reference'] = $agentService->reference;
            } else {
                $payload['nin'] = $agentService->nin;
            }

            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->acceptJson()
                ->get($url, $payload);
            
            if ($response->successful()) {
                $apiResponse = $response->json();
                $cleanResponse = $this->cleanApiResponse($apiResponse);

                $updateData = [
                    'comment' => $cleanResponse,
                ];

                $data = $apiResponse['data'] ?? $apiResponse;

                if (isset($data['status'])) {
                    $updateData['status'] = $this->normalizeStatus($data['status']);
                } elseif (isset($apiResponse['status'])) {
                    $updateData['status'] = $this->normalizeStatus($apiResponse['status']);
                } elseif (isset($apiResponse['response'])) {
                    $updateData['status'] = $this->normalizeStatus($apiResponse['response']);
                }

                $agentService->update($updateData);
            } else {
                $apiResponse = $response->json();
                $errorMessage = $apiResponse['message'] ?? 'API responded with an error.';
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to check status: ' . $errorMessage,
                    ], 400);
                }
                return back()->with('error', 'Status check failed: ' . $errorMessage);
            }

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'nin' => $agentService->nin,
                    'status' => $agentService->status,
                    'response' => $apiResponse,
                ]);
            }

            return back()->with('success', 'Status checked successfully. Current status: ' . $agentService->status);

        } catch (\Exception $e) {
            Log::error('Status Check Error: ' . $e->getMessage());
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check status: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Unable to complete the status check. Please try again.');
        }
    }


    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('NIN Validation Webhook Received', $data);

        $identifier = $data['nin'] ?? null;

        if ($identifier) {
            $submission = AgentService::where('nin', $identifier)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($submission) {
                $cleanResponse = $this->cleanApiResponse($data);
                
                $updateData = [
                    'comment' => $cleanResponse,
                ];

                if (isset($data['status'])) {
                    $updateData['status'] = $this->normalizeStatus($data['status']);
                }

                $submission->update($updateData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received successfully'
        ]);
    }

    private function cleanApiResponse($response): string
    {
        if (is_array($response)) {
            $data = $response['data'] ?? $response;
            // Prioritize human-readable message fields
            if (isset($data['comment']) && is_string($data['comment'])) {
                return $data['comment'];
            }
            if (isset($response['message']) && is_string($response['message'])) {
                return $response['message'];
            }
            if (isset($data['message']) && is_string($data['message'])) {
                return $data['message'];
            }

            // Exclude common structural keys and format the rest nicely
            $toExclude = ['status', 'success', 'nin', 'response', 'message', 'comment'];
            $toKeep = array_diff_key($response, array_flip($toExclude));

            if (empty($toKeep)) {
                return (isset($response['success']) && $response['success']) ? 'Successful' : 'Processed';
            }

            $parts = [];
            foreach ($toKeep as $key => $value) {
                $label = ucfirst(str_replace(['_', '-'], ' ', $key));
                if (is_bool($value)) {
                    $parts[] = $label . ': ' . ($value ? 'Yes' : 'No');
                } elseif (is_scalar($value)) {
                    $parts[] = $label . ': ' . $value;
                }
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
            'processing', 'in_progress', 'in-progress', 'pending', 'submitted', 'new' => 'processing',
            'failed', 'rejected', 'error', 'declined', 'invalid', 'no record' => 'failed',
            default => 'pending',
        };
    }
}