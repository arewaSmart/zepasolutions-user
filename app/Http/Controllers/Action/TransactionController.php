<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Transaction;
use App\Traits\ActiveUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class TransactionController extends Controller
{
    use ActiveUsers;

    public function show(Request $request)
    {

        $loginUserId = Auth::id();

        // Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        // Get filter values from the request
        $statusFilter = $request->input('status');
        $referenceFilter = $request->input('reference');
        $serviceTypeFilter = $request->input('service_type');

        // Get all transactions and apply filters
        $transactions = Transaction::where('user_id', $loginUserId)
            ->when($statusFilter, function ($query, $statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->when($referenceFilter, function ($query, $referenceFilter) {
                return $query->where('referenceId', 'like', "%$referenceFilter%");
            })
            ->when($serviceTypeFilter, function ($query, $serviceTypeFilter) {
                return $query->where('service_type', 'like', "%$serviceTypeFilter%");
            })

            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('transaction', [
            'transactions' => $transactions,
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    public function reciept(Request $request)
    {

        $loginUserId = Auth::id();

        // Retrieve the transaction based on the referenceId
        $transaction = Transaction::where('referenceId', $request->referenceId)
            ->where('user_id', $loginUserId)
            ->first();

        if (! $transaction) {
            // Handle case when the transaction is not found
            abort(404);
        }

        return view('receipt', ['transaction' => $transaction]);
    }

    public function validatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
        ]);

        $userId = auth()->id(); // Get the authenticated user ID
        $rateLimitKey = 'pin-attempts:'.$userId;

        // Check if the user has reached the limit
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $secondsUntilUnlock = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please try again after '.gmdate('i:s', $secondsUntilUnlock).' minutes.',
            ]);
        }

        $enteredPin = $request->input('pin');

        if ($this->isPinValid($enteredPin)) {
            // Clear the rate limiter on success
            RateLimiter::clear($rateLimitKey);

            return response()->json([
                'success' => true,
                'message' => 'PIN verified successfully.',
            ]);
        } else {
            // Increment the rate limiter on failure
            RateLimiter::hit($rateLimitKey, 900); // Lockout for 15 minutes

            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN. Please try again.',
            ]);
        }
    }

    private function isPinValid($pin)
    {
        $setPin = auth()->user()->pin; // Retrieve the hashed PIN for the authenticated user

        // Check if the stored hashed PIN is null or empty
        if (is_null($setPin) || empty($setPin)) {
            return false; // If no PIN is set, return false
        }

        // Verify the hashed PIN against the provided PIN
        return Hash::check($pin, $setPin); // Use Laravel's Hash facade to compare
    }
}
