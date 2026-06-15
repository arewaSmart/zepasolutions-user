<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SupportMail;
use App\Models\Notification;
use App\Traits\ActiveUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    use ActiveUsers;

    /**
     * Show the support page with form.
     */
    public function show()
    {
        // Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $loginUserId = Auth::id();

        $notifications = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        return view('support', [
            'notifications' => $notifications,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    /**
     * Send support mail.
     */
    public function send(Request $request)
    {
        // Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        try {
            Mail::to('customercare@zepasolutions.com')
                ->send(new SupportMail(
                    $request->input('subject'),
                    $request->input('message'),
                    $user
                ));

            return redirect()->back()->with('success', 'Your message has been sent to support successfully. We will reply to your email address shortly!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to send message. Please try again later. Error: ' . $e->getMessage())->withInput();
        }
    }
}
