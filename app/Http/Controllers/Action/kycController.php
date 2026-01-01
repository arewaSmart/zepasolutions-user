<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Mail\admin_kyc_notify_mail;
use App\Mail\kyc_notify_mail;
use App\Models\User;
use App\Traits\ActiveUsers;
use App\Traits\KycVerify;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class kycController extends Controller
{
    use ActiveUsers;
    use KycVerify;

    public function index()
    {
        // Check if user is Disabled
        if ($this->is_active() != 1) {
            Auth::logout();

            return view('error');
        }

        // Check if user is pending KYC
        if ($this->is_verified() == 'Pending') {
            return view('kyc');
        } else {
            // Return to dashboard to determine appropriate route
            return redirect()->route('dashboard');
        }
    }

    public function upload_from_disk(Request $request)
    {
        $request->validate(
            [
                'file' => 'image|mimes:jpeg,png,jpg|max:500',
            ],
            [
                // Passport
                'file.required' => 'Your Recent Passport is Required',
                'file.image' => 'The Passport must be a file of type: jpeg, png, jpg.',
                'file.mimes' => 'The Passport must be a file of type: jpeg, png, jpg.',
                'file.max' => 'The Passport must not be greater than 500 kilobytes.',
            ]
        );

        if ($request->file('file')) {

            $path = $request->file('file');
            $data = file_get_contents($path);
            $image_path = base64_encode($data);

            return response()->json(['status' => 200, 'img' => $image_path]);
        } else {
            return response()->json(['status' => 400]);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'User_Photo' => ['required', 'string'],
                'Identity_Number' => 'required|numeric|digits:11|unique:users,idNumber',
                'Identity_Type' => 'required|string|in:BVN',
                'Phone_Number' => 'required|numeric|digits:11|unique:users,phone_number',
                'Date_Of_Birth' => ['required', 'date'],
                'Middle_Name' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
                'Last_Name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
                'First_Name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],

            ],
            [
                // Validation Custom Messages
                'Phone_Number.unique' => 'The phone number is already associated with an existing user',
                'Identity_Number.unique' => 'The identification number is already associated with an existing user',
                'Identity_Type.required' => 'Please select a valid Identity type from the list',

                'Middle_Name.regex' => 'The middle name field does not match the required format',

            ]
        );

        $dobObject = new DateTime(date('Y-m-d', strtotime($request->Date_Of_Birth)));
        $nowObject = new DateTime;

        if ($dobObject->diff($nowObject)->y < 16) {
            return response()->json([
                'message' => 'Age limit must be 16 or Above',
                'errors' => ['Date of Birth' => 'Please provide your date of birth (DOB) if you are 16 years old or above.'],
            ], 422);
        }

        $loginUserId = Auth::id();
        $affected = User::where('id', $loginUserId)
            ->update([
                'first_name' => ucwords(strtolower($request->First_Name)),
                'middle_name' => ucwords(strtolower($request->Middle_Name)),
                'last_name' => ucwords(strtolower($request->Last_Name)),
                'dob' => $request->Date_Of_Birth,
                'phone_number' => $request->Phone_Number,
                'idNumber' => $request->Identity_Number,
                'idType' => $request->Identity_Type,
                'profile_pic' => $request->User_Photo,
                'kyc_status' => 'Submitted',
            ]);

        if ($affected) {

            // Get User Email Id
            $email = Auth::user()->email;

            $mail_data = [
                'type' => 'Submitted',
                'name' => ucwords(strtolower($request->First_Name)),
            ];

            try {

                Mail::to($email)->send(new kyc_notify_mail($mail_data));
                Mail::to('notification@zepasolutions.com')->send(new admin_kyc_notify_mail($mail_data));

            } catch (\Exception $e) {

                Log::error('Email sending failed: '.$e->getMessage());
            }
        }

        return response()->json(['status' => 200]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $loginUserId = Auth::id();
        User::where('id', $loginUserId)
            ->update([
                'phone_number' => null,
                'idNumber' => null,
                'idType' => null,
                'kyc_status' => 'Pending',
            ]);

        return response()->json(['status' => 200, 'redirect_url' => 'kyc']);
    }
}
