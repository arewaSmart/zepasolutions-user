<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\BVN_ENROLLMENT;
use App\Models\CRM_REQUEST;
use App\Models\CRM_REQUEST2;
use App\Models\NIN_REQUEST;
use App\Models\VNIN_TO_NIBSS;
use App\Models\Notification;
use App\Models\Services;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Traits\ActiveUsers;
use App\Traits\KycVerify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AgencyController extends Controller
{
    use ActiveUsers;
    use KycVerify;

    protected $walletService;

    protected $loginUserId;


    public function __construct(WalletService $walletService)
    {
        $this->loginUserId = Auth::id();
        $this->walletService = $walletService;
    }

    //Show CRM
    public function showCRM(Request $request)
    {


        //Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }


        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;


        //Notification Data
        $pending = CRM_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = CRM_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = CRM_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = CRM_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->count();

        $crm = CRM_REQUEST::where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $fee = 0;
        $fee  = Services::where('service_code', '110')->first();
        $ServiceFee =  $fee->amount;

        return view('crm', [
            'notifications' => $notifications,
            'ServiceFee' => $ServiceFee,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'crm' => $crm,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }


    //Submit Request
    public function crmRequest(Request $request)
    {
        $request->validate([
            // 'ticket_id' => 'required|numeric|digits:20',
            'bms_id' =>  'required|numeric|digits:21',
        ]);

        //Check if ticket id existed
        // $exist = CRM_REQUEST::where('ticket_no', $request->ticket_id)
        // ->where('bms_ticket_no', $request->bms_id)
        // ->exists();
        // $ticketExists = CRM_REQUEST::where('ticket_no', $request->ticket_id)->exists();
        $bmsExists = CRM_REQUEST::where('bms_ticket_no', $request->bms_id)->exists();

        if ($bmsExists) {
            return redirect()->back()->with('error', 'Sorry BMS ID already existed!');
        }

        $count = CRM_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 10) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.');
        }

        // $ticket_id = $request->ticket_id;
        $bms_id = $request->bms_id;

        // Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '110')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  <  $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $gen = "123456123456789071234567890890";
            $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }



            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'CRM Request',
                'service_description' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            CRM_REQUEST::create([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'bms_ticket_no' => $bms_id,
                // 'ticket_no' => $ticket_id,
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'CRM Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'CRM Request was successfully';
            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function showCRM2(Request $request)
    {

        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        //Notification Data
        $pending = CRM_REQUEST2::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = CRM_REQUEST2::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = CRM_REQUEST2::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = CRM_REQUEST2::all()
            ->where('user_id', $this->loginUserId)
            ->count();

        $crm = CRM_REQUEST2::where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $fee = 0;
        $fee  = Services::where('service_code', '111')->first();
        $ServiceFee =  $fee->amount;

        return view('crm2', [
            'notifications' => $notifications,
            'ServiceFee' => $ServiceFee,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'crm' => $crm,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }


    //Submit Request
    public function crmRequest2(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|digits:11',
            'surname' =>  'required|string',
        ]);

        //Check if ticket id existed
        $exist = CRM_REQUEST2::where('phoneno', $request->phone_number)
            ->where('dob', $request->dob)
            ->where('status', 'pending')
            ->exists();
        if ($exist) {
            return redirect()->back()->with('error', 'Sorry Request Already exist!');
        }

        $count = CRM_REQUEST2::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 10) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.');
        }

        $phoneno = $request->phone_number;
        $dob = $request->dob;
        $surname = $request->surname;

        // Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '111')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  <  $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $gen = "123456123456789071234567890890";
            $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }



            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'CRM Request WPD',
                'service_description' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            CRM_REQUEST2::create([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'phoneno' => $phoneno,
                'dob' => $dob,
                'surname' => $surname,
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'CRM Request WPD',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'CRM Request WPD was successfully';
            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function showBVN(Request $request)
    {

        //Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        //Notification Data
        $pending = DB::table('bvn_modifications')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = DB::table('bvn_modifications')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = DB::table('bvn_modifications')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = DB::table('bvn_modifications')
            ->where('user_id', $this->loginUserId)
            ->count();

        $mod = DB::table('bvn_modifications')->where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);


        $services = Services::where('category', 'Agency')
            ->where('type', 'BMOD')
            ->where('status', 'enabled')
            ->get();

        return view('bvn-mod', compact(
            'notifications',
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'mod',
            'services',
            'notifyCount',
            'notificationsEnabled'
        ));
    }

    public function bvnModRequest(Request $request)
    {

        $request->validate([
            'bvn_number' => 'required|numeric|digits:11',
            'enrollment_center' => [
                'required',
                'string',
                'in:First Bank,Heritage Bank,Agency Banking,Taj Bank,GTbank,Wema Bank,FCMB,Zenith Bank,Access Bank,Keystone Bank',
            ],
            'field_to_modify' => ['required', 'string'],
            'data_to_modify' => ['required', 'string'],
            'documents' => 'required|file|mimes:pdf|max:10240',
        ]);


        // if ($request->enrollment_center === 'Agency Banking') {
        //       $request->validate([
        //       'email' => 'required|email',
        //      'password' => ['required', 'string'],

        //     ]);
        // } 'enrollment_center' => ['required', 'string'],


        // //Check if ticket id existed
        // $exist = DB::table('bvn_modifications')
        //     ->where('type', $request->field_to_modify)
        //     ->where('status', 'pending')
        //     ->exists();
        // if ($exist) {
        //     return redirect()->back()->with('error', 'Your request is being processed. We appreciate your patience and will respond as soon as possible.');
        // }

        $count = DB::table('bvn_modifications')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 20) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.');
        }

        // Services Fee
        $ServiceFee = 0;
        $Service = Services::where('service_code', $request->field_to_modify)->first();
        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction! You need ₦'
                . number_format($ServiceFee, 2) . ' Complete the transaction');
        } else {

            // Retrieve the validated file
            $file = $request->file('documents');

            // Generate a unique file name or use the original name
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Move the file to the storage path
            $filePath = $file->storeAs('Documents', $fileName, 'public');

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = '';
            srand((float) microtime() * 1000000);
            $gen = '123456123456789071234567890890';
            $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
            $ddesc = '';
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }

            $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' => $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'BVN Modification Request',
                'service_description' => 'Wallet debitted with a request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            DB::table('bvn_modifications')->insert([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'enrollment_center' => $request->enrollment_center,
                'bvn_no' => $request->bvn_number,
                'type' => $serviceType,
                'data_to_modify' => $request->data_to_modify,
                'docs' => $filePath,
                'email' => $request->email ?? null,
                'password' => $request->password ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'BVN Modification Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'BVN Modification Request was successfully';

            return redirect()->back()->with('success', $successMessage);
        }
    }
    public function showEnrollment(Request $request)
    {

        //Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }



        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        $pending = BVN_ENROLLMENT::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'submitted')
            ->count();

        $resolved = BVN_ENROLLMENT::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'successful')
            ->count();

        $rejected = BVN_ENROLLMENT::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = BVN_ENROLLMENT::all()
            ->where('user_id', $this->loginUserId)
            ->count();

        $enrollments = BVN_ENROLLMENT::where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $fee = 0;
        $fee  = Services::where('service_code', '117')->first();
        $ServiceFee =  $fee->amount;

        return view('bvn-enrollment', [
            'notifications' => $notifications,
            'ServiceFee' => $ServiceFee,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'enrollments' => $enrollments,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    public function bvnEnrollmentRequest(Request $request)
    {
        $wallet_id  = $request->wallet_id;
        if ($request->enrollment_type     == 1) {

            $wallet_id = $request->phone;

            $request->validate([
                'enrollment_type' => 'required|numeric|digits:1',
                'phone' => 'required|numeric|digits:11',
                'username' => ['required', 'string'],
                'fullname' => ['required', 'string'],
                'state'  => ['required', 'string'],
                'lga'  => ['required', 'string'],
                'address'  => ['required', 'string'],
                'email' => 'required|email|unique:bvn_enrollments,email',
                'account_name' => 'required|string',
                'account_number' => 'required|digits:10',
                'bank_name' => 'required|string',
                'bvn' => 'required|digits:11',
            ]);
        } else {
            $request->validate([
                'enrollment_type' => 'required|numeric|digits:1',
                'wallet_id' => 'required|numeric|digits:11',
                'phone' => 'required|numeric|digits:11',
                'username' => ['required', 'string'],
                'fullname' => ['required', 'string'],
                'state'  => ['required', 'string'],
                'lga'  => ['required', 'string'],
                'address'  => ['required', 'string'],
                'email' => 'required|email|unique:bvn_enrollments,email',
                'account_name' => 'required|string',
                'account_number' => 'required|digits:10',
                'bank_name' => 'required|string',
                'bvn' => 'required|digits:11',
            ]);
        }

        // Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '117')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance  <  $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $gen = "123456123456789071234567890890";
            $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }



            $payer_name =  auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' =>  $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'BVN Enrollment Request',
                'service_description' => 'Wallet debitted with a request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            BVN_ENROLLMENT::create([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'wallet_id' => $wallet_id,
                'type' => $request->enrollment_type,
                'username' => $request->username,
                'fullname' => $request->fullname,
                'phone_number' => $request->phone,
                'email' => $request->email,
                'state' => $request->state,
                'lga' => $request->lga,
                'address' => $request->address,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'bank_name' => $request->bank_name,
                'bvn' => $request->bvn,
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'BVN Enrollment Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'BVN Enrollment Request Submitted';
            return redirect()->back()->with('success', $successMessage);
        }
    }
    public function showUpgrade(Request $request)
    {
        //Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }


        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;


        $pending = DB::table('account_upgrades')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = DB::table('account_upgrades')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = DB::table('account_upgrades')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = DB::table('account_upgrades')
            ->where('user_id', $this->loginUserId)
            ->count();

        $upgrades = DB::table('account_upgrades')->where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $fee = 0;
        $fee = Services::where('service_code', '118')->first();
        $ServiceFee = $fee->amount;

        return view('upgrade', [
            'notifications' => $notifications,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'upgrades' => $upgrades,
            'ServiceFee' => $ServiceFee,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    public function upgradeAccount(Request $request)
    {

        $request->validate([
            'documents' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Retrieve the validated file
        $file = $request->file('documents');

        // Generate a unique file name or use the original name
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Move the file to the storage path
        $filePath = $file->storeAs('Documents', $fileName, 'public');


        $count = DB::table('account_upgrades')
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 10) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending
        requests. Please wait until one of your requests is processed before submitting additional requests. Once a
        request is completed, you will be able to add more.');
        }


        // Services Fee
        $ServiceFee = 0;
        $ServiceFee = Services::where('service_code', '118')->first();
        $ServiceFee = $ServiceFee->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for
            Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = "";
            srand((float) microtime() * 1000000);
            $gen = "123456123456789071234567890890";
            $gen .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; // if you need alphabatic also
            $ddesc = "";
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }
            $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' => $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'BVN Upgrade Request',
                'service_description' => 'Wallet debitted with a request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            DB::table('account_upgrades')->insert([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'docs' => $filePath,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'Bank Upgrade Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'Account Upgrade Request was successfully';
            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function ninService(Request $request)
    {

        //Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        //Notification Data
        $pending = NIN_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = NIN_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = NIN_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = NIN_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->count();

        $nin = NIN_REQUEST::where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $services = Services::where('category', 'Agency')
            ->where('type', 'NIN')
            ->where('status', 'enabled')
            ->orderByRaw("FIELD(service_code, 137, 170) DESC")
            ->get();

        return view('nin-service', [
            'notifications' => $notifications,
            'services' => $services,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'nin' => $nin,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }

    public function ninServiceRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tracking_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array(strlen($value), [11, 15])) {
                        $fail('Tracking ID must be 15 digits and NIN 11 digits.');
                    }
                },
            ],
            'service' => 'required|numeric',
            'description' => 'sometimes|required|string',
        ], [
            'documents.required' => 'Photograph is required.',
            'documents.mimes'    => 'Photograph must be a file of type: jpeg, png.',
            'documents.max'      => 'Photograph must not be greater than 10MB.',
        ]);

        $validator->sometimes('documents', 'required|file|mimes:jpeg,png|max:10240', function ($input) {
            return in_array((int) $input->service, [163, 164, 165, 166]);
        });

        $validator->validate();

        $filePath = "";

        if ($request->hasFile('documents')) {
            // Retrieve the validated file
            $file = $request->file('documents');

            // Generate a unique file name or use the original name
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Move the file to the storage path
            $filePath = $file->storeAs('Uploads', $fileName, 'public');
        }


        // $trackingIdExists = NIN_REQUEST::where('trackingId', $request->tracking_id)->exists();

        // if ($trackingIdExists) {
        //     return redirect()->back()->with('error', 'Sorry  Tracking ID Or NIN Number already existed!');
        // }

        $count = NIN_REQUEST::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 10) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.');
        }

        $tracking_id = $request->tracking_id;

        $successMessage = 'NIN Service Request was successfully';

        // Services Fee
        $ServiceFee = 0;
        $Service = Services::where('service_code', $request->service)->first();
        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            if ($request->service == 170) {

                try {

                    $response = $this->pushForAutoIpe($tracking_id);

                    Log::info('Response:', $response);

                    if (isset($response['status']) && $response['status'] === true) {
                        //Flow continues;
                    } else {
                        return redirect()->back()->with('error', 'IPE request is not successful');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'IPE request is not successful');
                }
            }

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = '';
            srand((float) microtime() * 1000000);
            $gen = '123456123456789071234567890890';
            $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
            $ddesc = '';
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }

            $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' => $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'NIN Service Request',
                'service_description' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            NIN_REQUEST::create([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'trackingId' => $tracking_id,
                'service_type' => $serviceType,
                'description' => $request->description,
                'uploads' => $filePath,
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'NIN Service Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            // $this->walletService->creditDeveloperWallet($payer_name, $payer_email, $payer_phone, $referenceno . "C2w", "ipe_dev_fee");

            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function ipeRequestStatus($trackingId, $transactionId)
    {
        try {

            $data = ['trackingId' => $trackingId];

            $url = env('BASE_URL_VERIFY_USER') . 'api/v1/ipe-status';
            $token = env('VERIFY_USER_TOKEN');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];


            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['status']) && $response['status'] === true) {
                $data = $response['response'];


                if ($data['resp_code'] === '200') {


                    NIN_REQUEST::where('trackingId', $trackingId)
                        ->where('user_id', $this->loginUserId)
                        ->update(['reason' => $data['reply'] ?? '', 'status' => 'resolved']);

                    return redirect()->route('nin-services')
                        ->with('success', 'IPE request is successful, check the query section');
                } elseif ($data['resp_code'] === '101') {
                    NIN_REQUEST::where('trackingId', $trackingId)
                        ->where('user_id', $this->loginUserId)
                        ->update(['status' => 'Processing']);

                    return redirect()->route('nin-services')
                        ->with('error',  $response['message']);
                } elseif ($data['resp_code'] === '400') {
                    $this->refundIPE($transactionId, $response['message']);
                    return redirect()->route('nin-services')
                        ->with('error',  $response['message']);
                } else {
                    return redirect()->route('nin-services')
                        ->with('error',  $response['message']);
                }
            } elseif (isset($response['status']) && $response['status'] === false) {
                return redirect()->route('nin-services')
                    ->with('error',  $response['message']);
            } else {
                return redirect()->route('nin-services')
                    ->with('error', 'Unexpected error occurred');
            }
        } catch (\Exception $e) {

            return redirect()->route('nin-services')
                ->with('error', 'An error occurred while making the API request');
        }
    }
    public function pushForAutoIpe($trackingId)
    {

        try {

            $data = ['trackingId' => $trackingId];

            $url = env('BASE_URL_VERIFY_USER') . 'api/v1/ipe';
            $token = env('VERIFY_USER_TOKEN');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            return $response = json_decode($response, true);
        } catch (\Exception $e) {
        }
    }

    public function refundIPE($tnx, $message)
    {

        //Rejected
        NIN_REQUEST::where('tnx_id', $tnx)
            ->where('user_id', $this->loginUserId)
            ->update(['reason' => $message ?? '', 'status' => 'rejected']);

        // Check if already refunded
        $notRefunded = NIN_REQUEST::where('tnx_id', $tnx)
            ->whereNull('refunded_at')
            ->first();
        if ($notRefunded) {
            //get wallet balance
            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            $balance = 0;

            $serviceFee = Services::where('service_code', '170')->first();

            $balance = $wallet->balance + $serviceFee->amount;

            Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            //Keep History 
            $referenceno = '';
            srand((float) microtime() * 1000000);
            $gen = '123456123456789071234567890890';
            $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
            $ddesc = '';
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }

            $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' => $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => "IPE Rufund -" . $notRefunded->trackingId,
                'service_description' => 'Wallet Refunded with a Request fee of ' . number_format($serviceFee->amount, 2),
                'amount' => $serviceFee->amount,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            NIN_REQUEST::where('tnx_id', $tnx)
                ->update(['refunded_at' => Carbon::now()]);
        }
    }
    public function vninToNibss(Request $request)
    {

        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        $notifications = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $notifyCount = Notification::where('user_id', $this->loginUserId)
            ->where('status', 'unread')
            ->count();

        $notificationsEnabled = Auth::user()->notification;

        $pending = VNIN_TO_NIBSS::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        $resolved = VNIN_TO_NIBSS::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'resolved')
            ->count();

        $rejected = VNIN_TO_NIBSS::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'rejected')
            ->count();

        $total_request = VNIN_TO_NIBSS::all()
            ->where('user_id', $this->loginUserId)
            ->count();

        $vnin = VNIN_TO_NIBSS::where('user_id', $this->loginUserId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $fee = 0;
        $fee = Services::where('service_code', '148')
            ->where('status', 'enabled')
            ->first();
        $ServiceFee = $fee->amount;

        return view('vnin-to-nibss', [
            'notifications' => $notifications,
            'ServiceFee' => $ServiceFee,
            'pending' => $pending,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'total_request' => $total_request,
            'vnin' => $vnin,
            'notifyCount' => $notifyCount,
            'notificationsEnabled' => $notificationsEnabled,
        ]);
    }
    public function vninToNibssRequest(Request $request)
    {

        $request->validate([
            'request_id' => 'required|numeric|digits:7',
            'bvn_number' => 'required|numeric|digits:11',
            'nin_number' => 'required|numeric|digits:11',
        ]);

        $requestIdExists = VNIN_TO_NIBSS::where('requestId', $request->request_id)->exists();

        if ($requestIdExists) {
            return redirect()->back()->with('error', 'Sorry Request ID already existed!');
        }

        $count = VNIN_TO_NIBSS::all()
            ->where('user_id', $this->loginUserId)
            ->where('status', 'pending')
            ->count();

        if ($count == 10) {
            return redirect()->back()->with('error', 'Note: You have reached the maximum limit of ' . $count . ' Pending requests. Please wait until one of your requests is processed before submitting additional requests. Once a request is completed, you will be able to add more.');
        }

        // Services Fee
        $ServiceFee = 0;
        $Service = Services::where('service_code', 148)->first();
        $ServiceFee = $Service->amount;

        //Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginUserId)
                ->update(['balance' => $balance]);

            $referenceno = '';
            srand((float) microtime() * 1000000);
            $gen = '123456123456789071234567890890';
            $gen .= 'aBCdefghijklmn123opq45rs67tuv89wxyz'; // if you need alphabatic also
            $ddesc = '';
            for ($i = 0; $i < 12; $i++) {
                $referenceno .= substr($gen, (rand() % (strlen($gen))), 1);
            }

            $payer_name = auth()->user()->first_name . ' ' . Auth::user()->last_name;
            $payer_email = auth()->user()->email;
            $payer_phone = auth()->user()->phone_number;

            $trx_id = Transaction::create([
                'user_id' => $this->loginUserId,
                'payer_name' => $payer_name,
                'payer_email' => $payer_email,
                'payer_phone' => $payer_phone,
                'referenceId' => $referenceno,
                'service_type' => 'VNIN Service Request',
                'service_description' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
                'amount' => $ServiceFee,
                'gateway' => 'Wallet',
                'status' => 'Approved',
            ]);

            $trx_id = $trx_id->id;

            VNIN_TO_NIBSS::insert([
                'user_id' => $this->loginUserId,
                'tnx_id' => $trx_id,
                'refno' => $referenceno,
                'requestId' => $request->request_id,
                'nin_number' => $request->nin_number,
                'bvn_number' => $request->bvn_number,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            //Notifocation
            //In App Notification
            Notification::create([
                'user_id' => $this->loginUserId,
                'message_title' => 'VNIN Service Request',
                'messages' => 'Wallet debitted with a Request fee of ' . number_format($ServiceFee, 2),
            ]);

            $successMessage = 'VNIN Service Request was successfully';

            return redirect()->back()->with('success', $successMessage);
        }
    }
}
