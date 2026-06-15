<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Mail\PinUpdatedNotification;
use App\Models\Notification;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\Upgrade;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    // Class-level property
    protected $loginUserId;

    // Constructor to initialize the property
    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
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

        $is_enabled = User::where('id', $this->loginUserId)->value('notification');

        return view('profile.edit', compact(
            'notifications',
            'notifyCount',
            'is_enabled',
            'notificationsEnabled'
        ));
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        $request->validate([
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg|max:1028',
        ]);

        $user = Auth::user();

        // Convert image to base64 if uploaded
        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $base64Image = base64_encode(file_get_contents($image->getRealPath()));
            $user->profile_pic = $base64Image;
            $user->save();
        }

        return redirect()->back()->with('message', 'Profile picture updated successfully.');

    }

    public function passwordUpdate(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Retrieve the user
        $user = User::find($this->loginUserId);

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            abort('404');
        }

        // Check the current password
        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
            }
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->password_updated_at = now();

        // Save the user
        if ($user->save()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
            }
            return redirect()->back()->with('status', 'Password updated successfully.');
        } else {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update password.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to update password.');
        }
    }

    public function createPin(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'pin' => 'required|numeric|digits:4|confirmed',
        ]);

        $user = User::find(Auth::id() ?: $this->loginUserId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect account password.'], 422);
        }

        // Save new PIN
        $user->pin = bcrypt($request->pin);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Transaction PIN created successfully!']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function verifyPin(Request $request)
    {

        // Validate the password
        $request->validate([
            'password' => 'required|string',
        ]);

        // Check if the current password is correct
        if (! Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['error' => 'The provided password is incorrect.'], 422);
        }

        // Rate limiting for OTP requests
        $key = 'otp-request-'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 3)) {

            $seconds = RateLimiter::availableIn($key);

            return response()->json(['error' => 'Too many OTP requests. Please try again in '.ceil($seconds / 60).
                ' minutes.'], 429);
        }

        // Generate a one-time password (OTP)
        $otp = random_int(100000, 999999);
        $request->session()->put('otp', $otp);
        $request->session()->put('otp_expires', now()->addMinutes(5));

        // Send OTP to user's email or phone
        Mail::to(auth()->user()->email)->send(new OtpMail($otp, auth()->user()->first_name));

        // Increment the rate limiter
        RateLimiter::hit($key, 900);

        return response()->json(['success' => true, 'message' => 'OTP has been sent.']);
    }

    public function updatePin(Request $request)
    {
        // Validate the OTP
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        // Check if the OTP matches and hasn't expired
        $storedOtp = $request->session()->get('otp');
        $otpExpires = $request->session()->get('otp_expires');

        if (trim($request->otp) !== trim($storedOtp) || now()->greaterThan($otpExpires)) {
            return response()->json(['error' => 'The provided OTP is invalid or has expired.'], 422);
        }

        // Validate the new PIN
        $request->validate([
            'pin' => 'required|string|min:4|max:4', // Example for a 4-digit PIN
        ]);

        // Check if the user already has a PIN
        $user = auth()->user();

        // Create or update the user's PIN
        $user->pin = bcrypt($request->pin); // Store hashed PIN
        $user->save();

        // Clear the session variables
        $request->session()->forget(['otp', 'otp_expires']);

        Mail::to($user->email)
            ->send(new PinUpdatedNotification($user->first_name));

        return response()->json(['success' => true, 'message' => 'PIN updated successfully.']);
    }

    public function notify(Request $request)
    {
        // Get the logged-in user
        $user = Auth::user();

        // Validate the checkbox value (boolean or null)
        $isEnabled = $request->has('notification_sound');

        // Update user's notification sound preference
        $user->notification = $isEnabled;
        $user->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Notification settings updated successfully!');
    }

    public function upgrade(Request $request)
    {

        if ($request->type != 'agent') {
            return response()->json([
                'message' => 'Invalid Request Type',
                'errors' => ['Invalid Request Type' => 'Request Not Allowed'],
            ], 422);
        } else {

            $loginUserId = Auth::id();
            // Services Fee
            $ServiceFee = Services::where('service_code', '105')->first()->amount;

            // Check if wallet is funded
            try {
                \Illuminate\Support\Facades\DB::transaction(function() use ($loginUserId, $ServiceFee) {
                    $wallet = Wallet::where('user_id', $loginUserId)->lockForUpdate()->first();
                    if (!$wallet) {
                        throw new \Exception('Wallet not found.');
                    }

                    // Re-check count inside transaction to prevent concurrent duplicate requests
                    $count = Upgrade::where('user_id', $loginUserId)->count();
                    if ($count > 0) {
                        throw new \Exception('We are reviewing your request. will get back to you. if your request is not successfull you will be refunded');
                    }

                    if ($wallet->balance < $ServiceFee) {
                        throw new \Exception('Sorry Wallet Not Sufficient for Transaction !');
                    }

                    // Deduct balance
                    $wallet->decrement('balance', $ServiceFee);

                    $referenceno = '';
                    srand((float) microtime() * 1000000);
                    $data = '123456123456789071234567890890';
                    $data .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';
                    for ($i = 0; $i < 12; $i++) {
                        $referenceno .= substr($data, (rand() % (strlen($data))), 1);
                    }

                    $payer_name = auth()->user()->first_name.' '.Auth::user()->last_name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    $transaction = Transaction::create([
                        'user_id' => $loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $referenceno,
                        'service_type' => 'Account Upgrade Request',
                        'service_description' => 'Wallet debitted with Upgrade Fee of ₦'.number_format($ServiceFee, 2),
                        'amount' => $ServiceFee,
                        'gateway' => 'Wallet',
                        'status' => 'Pending',
                    ]);

                    Upgrade::create([
                        'user_id' => $loginUserId,
                        'user_name' => $payer_name,
                        'tnx_id' => $transaction->id,
                        'refno' => $transaction->referenceId,
                        'type' => 'Agent Upgrade',
                        'status' => 'Pending',
                    ]);
                });

                return response()->json(['status' => 200, 'msg' => 'Upgrade Request Submitted']);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error',
                    'errors' => ['Wallet Error' => $e->getMessage()],
                ], 422);
            }
        }
    }
}
