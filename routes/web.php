<?php

use App\Http\Controllers\Action\AgencyController;
use App\Http\Controllers\Action\BankVerificationController;
use App\Http\Controllers\Action\BVNController;
use App\Http\Controllers\Action\DashboardController;
use App\Http\Controllers\Action\NotificationController;
use App\Http\Controllers\Action\ServicesController;
use App\Http\Controllers\Action\UtilityController;
use App\Http\Controllers\Action\WalletController;
use App\Http\Controllers\NIN\NINController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Action\TransactionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Action\BankController;
use App\Http\Controllers\PaymentWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/pricing', function () {
    return view('pricing');
});

Route::get('/download-app', function () {

    $userAgent = request()->header('User-Agent'); // Laravel way to get the User-Agent
    $isAndroid = strpos($userAgent, 'Android') !== false;

    // If not Android, redirect to another page
    if (!$isAndroid) {
        $error = request()->query('error', 'Device not eligible. Please use an Android device.');
        return view('eligible', ['error' => $error]);
    }

    // Return the view for Android devices
    return view('download-app');
});


Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
});

// routes/web.php
Route::get('/terms-and-conditions', function () {
    $path = 'docs/terms-and-conditions.pdf';
    return response()->file($path);
})->name('terms');


Route::post('/palmpay/webhook', [PaymentWebhookController::class, 'handleWebhook']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/verify-user', [DashboardController::class, 'verifyUser'])->name('verify-user');
});

Route::middleware('auth', 'verified', 'is_kyced')->group(function () {

    //General
    Route::post('/read', [NotificationController::class, 'read'])->name('read');

    //Dashboard
    // Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    //Transaction
    Route::get('/transactions', [DashboardController::class, 'show'])->name('transactions');



    //BVN Verify
    Route::get('/bvn', [BVNController::class, 'show'])->name('bvn');
    Route::post('/retrieveBVN', [BVNController::class, 'retrieveBVN'])->name('retrieve-bvn');
    Route::get('/bvn2', [BVNController::class, 'show'])->name('bvn2');
    Route::post('/bvnv2-retrieve', [BVNController::class, 'bvnV2Retrieve'])->name('bvnV2Retrieve');

    //NIN Verification
    Route::get('/nin', [NINController::class, 'show'])->name('nin');
    Route::post('/retrieveNIN', [NINController::class, 'retrieveNIN'])->name('retrieve-nin');
    Route::post('/retrieveNIN2', [NINController::class, 'retrieveNIN2'])->name('retrieve-nin-track');


    Route::get('/nin-phone', [NINController::class, 'show'])->name('nin-phone');
    Route::get('/nin-vnin', [NINController::class, 'show'])->name('nin-vnin');
    Route::get('/nin-demographic', [NINController::class, 'show'])->name('nin-demo');
    Route::get('/nin-track', [NINController::class, 'show'])->name('nin-track');

    //NIN Version 2
    Route::get('/nin2', [NINController::class, 'ninV2Form'])->name('nin2');
    Route::post('/nin2-retrieve', [NINController::class, 'ninV2Retrieve'])->name('ninV2Retrieve');


    //Bank Verify
    Route::get('/bank', [BankVerificationController::class, 'show'])->name('bank');
    Route::post('/retrieveBank', [BankVerificationController::class, 'retrieveBank'])->name('retrieve-bank');

    // Route::get('/fetchBanksws', [BankVerificationController::class, 'fetchBanksws']);


    //Clain & Transfer
    Route::get('claim', [WalletController::class, 'claim'])->name('claim');
    Route::get('claim-bonus/{id}', [WalletController::class, 'claimBonus'])->name('claim-bonus');
    Route::get('p2p',  [WalletController::class, 'p2p'])->name('p2p');
    Route::get('getReciever',  [WalletController::class, 'getReciever']);
    Route::get('funding',  [WalletController::class, 'funding'])->name('funding');
    Route::post('transfer-funds',  [WalletController::class, 'transfer'])->name('transfer-funds');

    //Begin Agency Services--------------------------------------------------------------------------------
    Route::middleware(['check.agent'])->group(function () {
        Route::get('crm', [AgencyController::class, 'showCRM'])->name('crm');
        Route::post('crm-request',  [AgencyController::class, 'crmRequest'])->name('crmRequest');

        Route::get('crm2', [AgencyController::class, 'showCRM2'])->name('crm2');
        Route::post('crm-request2',  [AgencyController::class, 'crmRequest2'])->name('crmRequest2');

        Route::get('bvn-modification', [AgencyController::class, 'showBVN'])->name('bvn-modification');
        Route::post('modify-bvn',  [AgencyController::class, 'bvnModRequest'])->name('modify-bvn');

        Route::get('nin-services', [AgencyController::class, 'ninService'])->name('nin-services');
        Route::post('request-nin-service', [AgencyController::class, 'ninServiceRequest'])->name('request-nin-service');

        Route::get('/ipeStatus/{id}/{transaction}', [AgencyController::class, 'ipeRequestStatus'])->name('ipeStatus');

        Route::get('vnin-to-nibss', [AgencyController::class, 'vninToNibss'])->name('vnin-to-nibss');
        Route::post('request-vnin-to-nibss', [AgencyController::class, 'vninToNibssRequest'])->name('request-vnin-to-nibss');


        Route::get('bvn-enrollment', [AgencyController::class, 'showEnrollment'])->name('bvn-enrollment');
        Route::post('request-enrollment',  [AgencyController::class, 'bvnEnrollmentRequest'])->name('enroll');
        Route::get('getUserdetails',  [WalletController::class, 'getUserdetails']);

        Route::get('account-upgrade', [AgencyController::class, 'showUpgrade'])->name('account-upgrade');
        Route::post('upgrade-account', [AgencyController::class, 'upgradeAccount'])->name('upgrade-account');

        Route::get('/wema-bank', function () {
            $path = 'docs/wema.pdf';
            return response()->file($path);
        })->name('wema');

        Route::get('/gtb-bank', function () {
            $path = 'docs/gtb.pdf';
            return response()->file($path);
        })->name('gtb');
    });
    //End Agency Services. ---------------------------------------------------------------------------------

    //Whatsapp API Support Routes--------------------------------------------------------------------------
    Route::get('/support', function () {
        $phoneNumber = env('phoneNumber');
        $message = urlencode(env('message'));
        $url = env('API_URL') . "{$phoneNumber}&text={$message}";
        return redirect($url);
    })->name('support');
    //End Whatsapp API Support Routes ------------------------------------------------------------------------------------------


    //PDF Downloads -----------------------------------------------------------------------------------------------------
    Route::get('/standardBVN/{id}', [BVNController::class, 'standardBVN'])->name("standardBVN");
    Route::get('/premiumBVN/{id}', [BVNController::class, 'premiumBVN'])->name("premiumBVN");
    Route::get('/regularSlip/{id}', [NINController::class, 'regularSlip'])->name("regularSlip");
    Route::get('/standardSlip/{id}', [NINController::class, 'standardSlip'])->name("standardSlip");
    Route::get('/premiumSlip/{id}', [NINController::class, 'premiumSlip'])->name("premiumSlip");
    //End PDF Downloads Routes ------------------------------------------------------------------------------------------


    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password-update', [ProfileController::class, 'passwordUpdate'])->name('profile.password');
    Route::post('/pin-verify', [ProfileController::class, 'verifyPin'])->name('pin.verify');
    Route::post('/pin-update', [ProfileController::class, 'updatePin'])->name('pin.update');
    Route::post('/notification', [ProfileController::class, 'update'])->name('notification.update');
    Route::post('/notification/update', [ProfileController::class, 'notify'])->name('notification.update');


    //Account Upgrade Routes
    Route::post('/upgrade', [ProfileController::class, 'upgrade'])->name('upgrade');


    Route::get('/airtime', [UtilityController::class, 'airtime'])->name('airtime');
    Route::post('/buy-airtime', [UtilityController::class, 'buyAirtime'])->name('buyairtime');

    Route::get('/data', [UtilityController::class, 'data'])->name('data');
    Route::post('/buy-data', [UtilityController::class, 'buydata'])->name('buydata');
    Route::get('/fetch-data-bundles', [UtilityController::class, 'fetchBundles']);
    Route::get('/fetch-data-bundles-price', [UtilityController::class, 'fetchBundlePrice']);

    Route::get('/sme-data', [UtilityController::class, 'data'])->name('sme-data');
    Route::get('/fetch-data-type', [UtilityController::class, 'fetchDataType']);
    Route::get('/fetch-data-plan', [UtilityController::class, 'fetchDataPlan']);
    Route::get('/fetch-sme-data-bundles-price', [UtilityController::class, 'fetchSmeBundlePrice']);
    Route::post('/buy-sme-data', [UtilityController::class, 'buySMEdata'])->name('buy-sme-data');

    Route::get('/education', [UtilityController::class, 'pin'])->name("education");
    Route::post('/buy-pin', [UtilityController::class, 'buypin'])->name('buypin');

    Route::get('/transactions', [TransactionController::class, 'show'])->name("transactions");

    Route::get('/tv', [ServicesController::class, 'show'])->name("tv");
    Route::get('/electricity', [ServicesController::class, 'show'])->name("electricity");

    //More Services
    Route::get('/services/{name}', [ServicesController::class, 'show'])->name("more-services");
    //Generate Reciept
    Route::get('/receipt/{referenceId}', function ($referenceId) {
        $loginUserId = Auth::id();
        // Retrieve the transaction based on the referenceId
        $transaction = Transaction::where('referenceId', $referenceId)
            ->where('user_id', $loginUserId)
            ->first();

        if (!$transaction) {
            // Handle case when the transaction is not found
            abort(404);
        }

        return view('receipt', ['transaction' => $transaction]);
    })->name('reciept');
    //End --------------------------------------------------------------------
    Route::post('/verifyPayments', [WalletController::class, 'verify'])->name('verify');

    //Payout
    Route::get('/transfer', [WalletController::class, 'showpayout'])->name('transfer');
    Route::get('/verifyBankAccount', [BankController::class, 'verifyBankAccount'])->name('verifyBankAccount');
    Route::post('/payout', [WalletController::class, 'payout'])->name('payout');
    Route::post('/validatePin', [TransactionController::class, 'validatePin'])->name('pin.validate');
    Route::get('/fetchBanks', [BankController::class, 'fetchBanks'])->name('fetchBanks');

    Route::post('/funding-request', [WalletController::class, 'store'])->name('funding-request');
});

require __DIR__ . '/auth.php';
