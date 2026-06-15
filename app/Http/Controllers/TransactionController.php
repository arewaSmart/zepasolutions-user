<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Transaction;
use App\Traits\ActiveUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                return $query->where(function ($q) use ($referenceFilter) {
                    $q->where('referenceId', 'like', "%$referenceFilter%")
                      ->orWhere('transaction_ref', 'like', "%$referenceFilter%");
                });
            })
            ->when($serviceTypeFilter, function ($query, $serviceTypeFilter) {
                return $query->where(function ($q) use ($serviceTypeFilter) {
                    $q->where('service_type', 'like', "%$serviceTypeFilter%")
                      ->orWhere('type', 'like', "%$serviceTypeFilter%")
                      ->orWhere('metadata->service', 'like', "%$serviceTypeFilter%");
                });
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
}
