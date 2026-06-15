<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function read(Request $request)
    {
        $loginUserId = Auth::id();

        Notification::where('user_id', $loginUserId)->update(['status' => 'read']);
    }
}
