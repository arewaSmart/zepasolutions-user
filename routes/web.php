<?php

use App\Http\Controllers\Agency\BvncrmController;
use App\Http\Controllers\Agency\BvnUserController;
use App\Http\Controllers\Agency\BvnModificationController;
use App\Http\Controllers\Agency\NinModificationController;
use App\Http\Controllers\Agency\IpeController;
use App\Http\Controllers\Agency\ManualSearchController;

use App\Http\Controllers\Agency\NinValidationController;
use App\Http\Controllers\BankVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Action\UtilityController;
use App\Http\Controllers\WalletController;

// Verification Controllers
use App\Http\Controllers\Verifications\NINverificationController;
use App\Http\Controllers\Verifications\NINDemoVerificationController;
use App\Http\Controllers\Verifications\NINPhoneVerificationController;
use App\Http\Controllers\Verifications\BvnverificationController;

// Utility Controllers (Action)
use App\Http\Controllers\Action\AirtimeController;
use App\Http\Controllers\Action\DataController;
use App\Http\Controllers\Action\SmeDataController;
use App\Http\Controllers\Action\EducationalController;
use App\Http\Controllers\Action\ElectricityController;
use App\Http\Controllers\Action\CableController;

use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
Route::get('/terms-and-conditions', function () {
    $path = 'docs/terms-and-conditions.pdf';

    return response()->file($path);
})->name('terms');

Route::post('/palmpay/webhook', [PaymentWebhookController::class, 'handleWebhook']);
Route::post('/nin-validation/webhook', [NinValidationController::class, 'webhook'])->name('nin-validation.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/verify-user', [DashboardController::class, 'verifyUser'])->name('verify-user');
    Route::post('/save-profile-kyc', [DashboardController::class, 'saveProfileKyc'])->name('kyc.save-profile');
    
    // Support routes
    Route::get('/support', [SupportController::class, 'show'])->name('support');
    Route::post('/support', [SupportController::class, 'send'])->name('support.send')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'verified', 'is_kyced'])->group(function () {

    // General
    Route::post('/read', [NotificationController::class, 'read'])->name('read');

    /*
    |--------------------------------------------------------------------------
    | User Verification Services
    |--------------------------------------------------------------------------
    */

    Route::prefix('nin-verification')->group(function () {
        Route::get('/', [NINverificationController::class, 'index'])->name('nin.verification.index');
        Route::post('/', [NINverificationController::class, 'store'])->name('nin.verification.store')->middleware('throttle:15,1');
        Route::get('/standardSlip/{id}', [NINverificationController::class, 'standardSlip'])->name('standardSlip');
        Route::get('/regularSlip/{id}', [NINverificationController::class, 'regularSlip'])->name('regularSlip');
        Route::get('/premiumSlip/{id}', [NINverificationController::class, 'premiumSlip'])->name('premiumSlip');
        Route::get('/vninSlip/{id}', [NINverificationController::class, 'vninSlip'])->name('vninSlip');
    });

    Route::prefix('nin-demo-verification')->group(function () {
        Route::get('/', [NINDemoVerificationController::class, 'index'])->name('nin.demo.index');
        Route::post('/', [NINDemoVerificationController::class, 'store'])->name('nin.demo.store')->middleware('throttle:15,1');
        Route::get('/freeSlip/{id}', [NINDemoVerificationController::class, 'freeSlip'])->name('nin.demo.freeSlip');
        Route::get('/regularSlip/{id}', [NINDemoVerificationController::class, 'regularSlip'])->name('nin.demo.regularSlip');
        Route::get('/standardSlip/{id}', [NINDemoVerificationController::class, 'standardSlip'])->name('nin.demo.standardSlip');
        Route::get('/premiumSlip/{id}', [NINDemoVerificationController::class, 'premiumSlip'])->name('nin.demo.premiumSlip');
    });

    Route::prefix('nin-phone-verification')->group(function () {
        Route::get('/', [NINPhoneVerificationController::class, 'index'])->name('nin.phone.index');
        Route::post('/', [NINPhoneVerificationController::class, 'store'])->name('nin.phone.store')->middleware('throttle:15,1');
        Route::get('/regularSlip/{id}', [NINPhoneVerificationController::class, 'regularSlip'])->name('nin.phone.regularSlip');
        Route::get('/standardSlip/{id}', [NINPhoneVerificationController::class, 'standardSlip'])->name('nin.phone.standardSlip');
        Route::get('/premiumSlip/{id}', [NINPhoneVerificationController::class, 'premiumSlip'])->name('nin.phone.premiumSlip');
    });

    Route::prefix('bvn-verification')->group(function () {
        Route::get('/', [BvnverificationController::class, 'index'])->name('bvn.verification.index');
        Route::post('/', [BvnverificationController::class, 'store'])->name('bvn.verification.store')->middleware('throttle:15,1');
        Route::get('/standardBVN/{id}', [BvnverificationController::class, 'standardBVN'])->name("standardBVN");
        Route::get('/premiumBVN/{id}', [BvnverificationController::class, 'premiumBVN'])->name("premiumBVN");
        Route::get('/plasticBVN/{id}', [BvnverificationController::class, 'plasticBVN'])->name("plasticBVN");
    });

    /*
    |--------------------------------------------------------------------------
    | Service Utilities (Airtime, Data, Bills)
    |--------------------------------------------------------------------------
    */

    Route::prefix('airtime')->group(function () {
        Route::get('/', [AirtimeController::class, 'airtime'])->name('airtime');
        Route::post('/buy', [AirtimeController::class, 'buyAirtime'])->name('buyairtime')->middleware('throttle:10,1');
    });

    Route::prefix('data')->group(function () {
        Route::get('/', [DataController::class, 'data'])->name('buy-data');
        Route::post('/buy', [DataController::class, 'buydata'])->name('buydata')->middleware('throttle:10,1');
        Route::get('/fetch-bundles', [DataController::class, 'fetchBundles'])->name('fetch.bundles');
        Route::get('/fetch-price', [DataController::class, 'fetchBundlePrice'])->name('fetch.bundle.price');
        Route::post('/verify-pin', [DataController::class, 'verifyPin'])->name('verify.pin')->middleware('throttle:5,1');
    });

    Route::prefix('sme-data')->group(function () {
        Route::get('/', [SmeDataController::class, 'index'])->name('buy-sme-data');
        Route::post('/buy', [SmeDataController::class, 'buySMEdata'])->name('buy-sme-data.submit')->middleware('throttle:10,1');
        Route::get('/fetch-type', [SmeDataController::class, 'fetchDataType'])->name('sme.fetch.type');
        Route::get('/fetch-plan', [SmeDataController::class, 'fetchDataPlan'])->name('sme.fetch.plan');
        Route::get('/fetch-price', [SmeDataController::class, 'fetchSmeBundlePrice'])->name('sme.fetch.price');
    });

    Route::prefix('education')->group(function () {
        Route::get('/', [EducationalController::class, 'pin'])->name("education");
        Route::post('/buy-pin', [EducationalController::class, 'buypin'])->name('buypin')->middleware('throttle:10,1');
        Route::get('/get-variation', [EducationalController::class, 'getVariation'])->name('get-variation');
        Route::get('/jamb', [EducationalController::class, 'jamb'])->name('jamb');
        Route::post('/verify-jamb', [EducationalController::class, 'verifyJamb'])->name('verify.jamb')->middleware('throttle:15,1');
        Route::post('/buy-jamb', [EducationalController::class, 'buyJamb'])->name('buyjamb')->middleware('throttle:10,1');
    });

    Route::prefix('electricity')->group(function () {
        Route::get('/', [ElectricityController::class, 'index'])->name('electricity');
        Route::post('/verify', [ElectricityController::class, 'verifyMeter'])->name('verify.electricity')->middleware('throttle:15,1');
        Route::post('/buy', [ElectricityController::class, 'purchase'])->name('buy.electricity')->middleware('throttle:10,1');
    });

    Route::prefix('cable')->group(function () {
        Route::get('/', [CableController::class, 'index'])->name('cable');
        Route::get('/variations', [CableController::class, 'getVariations'])->name('cable.variations');
        Route::post('/verify', [CableController::class, 'verifyIuc'])->name('verify.cable')->middleware('throttle:15,1');
        Route::post('/buy', [CableController::class, 'purchase'])->name('buy.cable')->middleware('throttle:10,1');
    });

    // Claim & Transfer
    Route::get('claim', [WalletController::class, 'claim'])->name('claim');
    Route::get('claim-bonus/{id}', [WalletController::class, 'claimBonus'])->name('claim-bonus');
    Route::get('p2p', [WalletController::class, 'p2p'])->name('p2p');
    Route::get('getReciever', [WalletController::class, 'getReciever']);
    Route::get('funding', [WalletController::class, 'funding'])->name('funding');
    Route::post('transfer-funds', [WalletController::class, 'transfer'])->name('transfer-funds')->middleware('throttle:5,1');

    // Begin Agency Services--------------------------------------------------------------------------------
    Route::get('crm', [BvncrmController::class, 'index'])->name('crm');
    Route::post('crm-request', [BvncrmController::class, 'store'])->name('crm.store')->middleware('throttle:15,1');
    Route::get('crm/check/{id}', [BvncrmController::class, 'checkStatus'])->name('crm.check');

    Route::get('phone-search', [ManualSearchController::class, 'index'])->name('phone.search.index');
    Route::post('phone-search', [ManualSearchController::class, 'store'])->name('phone.search.store')->middleware('throttle:15,1');
    Route::get('phone-search/check/{id}', [ManualSearchController::class, 'checkStatus'])->name('phone.search.check');

    Route::redirect('crm2', 'phone-search')->name('crm2');

    Route::get('bvn-modification', [BvnModificationController::class, 'index'])->name('bvn-modification');
    Route::post('modify-bvn', [BvnModificationController::class, 'store'])->name('modify-bvn')->middleware('throttle:15,1');
    Route::get('bvn-modification/check/{id}', [BvnModificationController::class, 'checkStatus'])->name('bvn-modification.check');
    Route::get('modification-fields/{serviceId}', [BvnModificationController::class, 'getFields'])->name('modification.fields');

    Route::get('nin-services', [NinModificationController::class, 'index'])->name('nin-services');
    Route::post('request-nin-service', [NinModificationController::class, 'store'])->name('request-nin-service')->middleware('throttle:15,1');
    Route::get('/ninStatus/{id}/{transaction}', [NinModificationController::class, 'checkStatus'])->name('ninStatus');
    Route::get('nin-modification', [NinModificationController::class, 'index'])->name('nin-modification');
    Route::post('nin-modification', [NinModificationController::class, 'store'])->name('nin-modification.store')->middleware('throttle:15,1');
    Route::get('nin-modification/check/{id}', [NinModificationController::class, 'checkStatus'])->name('nin-modification.check');

    Route::get('/ipeStatus/{id}/{transaction}', [IpeController::class, 'check'])->name('ipeStatus');
    Route::get('ipe', [IpeController::class, 'index'])->name('ipe.index');
    Route::post('ipe', [IpeController::class, 'store'])->name('ipe.store')->middleware('throttle:15,1');
    Route::get('ipe/check/{id}', [IpeController::class, 'check'])->name('ipe.check');

    // NIN Validation
    Route::get('nin-validation', [NinValidationController::class, 'index'])->name('nin-validation.index');
    Route::post('nin-validation', [NinValidationController::class, 'store'])->name('nin-validation.store')->middleware('throttle:15,1');
    Route::get('nin-validation/check/{id?}', [NinValidationController::class, 'checkStatus'])->name('nin-validation.check');

    Route::get('getUserdetails', [WalletController::class, 'getUserdetails']);

    // End Agency Services. ---------------------------------------------------------------------------------

    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:15,1');
    Route::put('/password-update', [ProfileController::class, 'passwordUpdate'])->name('profile.password')->middleware('throttle:5,1');
    Route::post('/pin-verify', [ProfileController::class, 'verifyPin'])->name('pin.verify')->middleware('throttle:5,1');
    Route::post('/pin-update', [ProfileController::class, 'updatePin'])->name('pin.update')->middleware('throttle:5,1');
    Route::post('/pin-create', [ProfileController::class, 'createPin'])->name('pin.create')->middleware('throttle:5,1');

    Route::post('/notification', [ProfileController::class, 'update'])->name('notification.update');
    Route::post('/notification/update', [ProfileController::class, 'notify'])->name('notification.update');

    // Account Upgrade Routes
    Route::post('/upgrade', [ProfileController::class, 'upgrade'])->name('upgrade')->middleware('throttle:10,1');

    Route::get('/transactions', [TransactionController::class, 'show'])->name('transactions');

    // More Services
    Route::get('/services/{name}', [ServicesController::class, 'show'])->name('more-services');

    // Thank You Receipt Page
    Route::get('/thankyou', function () {
        $loginUserId = Auth::id();
        $ref = session('transaction_ref') ?? session('ref') ?? session('request_id');

        $transaction = null;
        if ($ref) {
            $transaction = Transaction::where('user_id', $loginUserId)
                ->where(function($query) use ($ref) {
                    $query->where('referenceId', $ref)
                          ->orWhere('transaction_ref', $ref);
                })
                ->first();
        }

        if (!$transaction) {
            return redirect()->route('dashboard')->with('error', 'No recent transaction found.');
        }

        return view('thankyou', compact('transaction'));
    })->name('thankyou');

});

require __DIR__.'/auth.php';
