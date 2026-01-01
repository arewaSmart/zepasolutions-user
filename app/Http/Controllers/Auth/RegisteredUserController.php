<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BonusHistory;
use App\Models\User;
use App\Models\Wallet;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:6', 'regex:/^[\pL\pN\s\-]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
        ]);

        $referralDetails = $this->getBonus($request);

        if (isset($referralDetails['error'])) {
            return response()->json([
                'message' => 'error',
                'errors' => [$referralDetails['error']],
            ], 422);
        }

        try {

            DB::beginTransaction();

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'referral_code' => $referralDetails['myOwnCode'],
                'refferral_id' => $referralDetails['referral_id'],
            ]);

            DB::commit();

            if ($referralDetails['referral_id']) {
                $this->addBonus($referralDetails['referral_id'], $referralDetails['referral_bonus'], $user->id);
            }

            $this->notificationService->createNotification(
                $user->id,
                'Welcome Message',
                'Welcome to Zepa Solutions, Your trusted partner for convenient verification, airtime, data, cable, products, and ID solutions.'
            );

            event(new Registered($user));
            Auth::login($user);

            return response()->json(['status' => 200, 'redirect_url' => url('dashboard')]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Registration error: '.$e->getMessage());

            return response()->json([
                'message' => 'error',
                'errors' => ['Registration failed. Please try again later.'],
            ], 422);
        }
    }

    private function getBonus($request)
    {
        $referral_id = null;
        $referral_bonus = 0.00;

        if ($request->filled('referral_code')) {

            $referralUser = User::where('referral_code', $request->referral_code)->first();

            if ($referralUser) {

                $referral_id = $referralUser->id;
                $referral_bonus = $referralUser->referral_bonus;

                if (bccomp($referral_bonus, '0.00', 2) === 0) {
                    $defaultBonus = DB::table('referral_bonus')->value('bonus');
                    if ($defaultBonus !== null) {
                        $referral_bonus = $defaultBonus;
                    }
                }
            } else {

                return ['error' => 'Invalid Referral Code. Please enter a valid one to proceed.'];
            }
        }

        do {
            $random = md5(uniqid($request->email, true));
            $myOwnCode = substr($random, 0, 6);
        } while (User::where('referral_code', $myOwnCode)->exists());

        return [
            'referral_id' => $referral_id,
            'referral_bonus' => $referral_bonus,
            'myOwnCode' => $myOwnCode,
        ];
    }

    private function addBonus($referral_id, $referral_bonus, $referred_user_id)
    {

        $exist = User::where('id', $referral_id)
            ->where('wallet_is_created', 1)
            ->exists();

        if ($exist) {

            $wallet = Wallet::where('user_id', $referral_id)->first();

            if ($wallet) {

                $bonus_balance = $wallet->bonus + $referral_bonus;

                Wallet::where('user_id', $referral_id)->update([
                    'bonus' => $bonus_balance,
                ]);

                BonusHistory::create([
                    'user_id' => $referral_id,
                    'referred_user_id' => $referred_user_id,
                    'amount' => $referral_bonus,
                ]);
            }
        }
    }
}
