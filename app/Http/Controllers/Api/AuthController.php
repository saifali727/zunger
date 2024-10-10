<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Otp};
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{
    public function signup(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'nick_name' => 'required',
            'dob' => 'required',
            'password' => 'required',
        ]);

        if ($request->email == "" && $request->phone_number == "") {
            return response()->json([
                'status' => '422',
                'message' => __('auth.please register through email or phone number'),
            ]);
        }

        // $validated->sometimes('email', 'required|unique:users', function ($input) {
        //     return $input->type_id == 3;
        // });

        if ($request->phone_number) {
            $number = Otp::where('phone_number', $request->phone_number)->first();
            // $email = Otp::where('email', $request->email)->first();
            if ($number) {
                if ($number->status == 1) {
                    $user = User::where('phone_number', $request->phone_number)->first();
                    if ($user) {
                        return response()->json([
                            "status" => "204",
                            "message" => __('auth.user already exist'),
                        ]);
                    } else {
                        $user =  User::create([
                            'phone_number' => $request->phone_number,
                            'nick_name' => $request->nick_name,
                            'dob' => $request->date_of_birth,
                            'password' => \Hash::make($request->password),
                            'user_name' => "user" . "" . rand(1111111111, 9999999999),
                            'fcm_token'=>$request->fcm_token,
                            'profile_image' => 'storage/user_image/aGXGaz2Rtcs3FpAMjDzuX1zIRBbDpK4im7DmdGqv.jpg'
                        ]);

                        if ($user) {

                            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                            return response()->json([
                                "status" => "200",
                                "message" => __('auth.user signup successfully'),
                                'user' => $user,
                                'token' => $token,
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        "status" => "204",
                        "message" => __('auth.phone number not verfied'),
                    ]);
                }
            } else {
                return response()->json([
                    "status" => "404",
                    "message" => __('auth.number does not exist'),
                ]);
            }
        } else if ($request->email) {
            $user = User::create([
                'email' => $request->email,
                'nick_name' => $request->nick_name,
                'user_name' => "user" . "" . rand(1111111111, 9999999999),
                'dob' => $request->date_of_birth,
                'password' => Hash::make($request->password),
            ]);

            if ($user) {
                return response()->json([
                    "status" => "200",
                    "message" => __('auth.user signup successfully'),
                    'user' => $user,
                ]);
            } else {
                return response()->json([
                    "status" => "204",
                    "message" => __('auth.signup error'),
                ]);
            }
        } else {
            return response()->json([
                'status' => 204,
                'message' => __('auth.Please provide phone_number and mail'),
            ]);
        }
    }

    public function login(Request $request)
    {
        App::setLocale($request->locale);
        // return Hash::make('12345678');
        $user = "";
        // $user = User::where('email', $request->email)->first();
        // return $user;
        $validated = $request->validate([
            'password' => 'required',
            'fcm_token'=>'required'
        ]);
        if ($request->email == "" && $request->phone_number == "") {
            return response()->json([
                'status' => '422',
                'message' => __('auth.please login through email or phone number')
            ]);
        }
        if ($request->phone_number) {
            $user = User::where('phone_number', $request->phone_number)->first();
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        }

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $user->update(['fcm_token'=>$request->fcm_token]);
                return response()->json([
                    "status" => "200",
                    "message" => __('auth.user sigin successfully'),
                    'user' => $user,
                    'token' => $token,
                ]);
            } else {
                return response()->json([
                    "status" => "204",
                    "message" => "password incorrect",
                ]);
            }
        } else {
            return response()->json([
                "status" => "204",
                "message" => ('auth.user does not exist'),
            ]);
        }
    }

    public function number_verification(Request $request)
    {
        App::setLocale($request->locale);
        //    return rand(111111, 999999)
        $verification = "";
        $validated = $request->validate([
            'phone_number' => 'required|unique:users',
            'channel' => 'required'
        ]);
        $number = Otp::where('phone_number', $request->phone_number)->first();
        try{
            if ($number) {
                if ($number->status ==  1) {
                    return response()->json([
                        'status' => "200",
                        "message" => __('auth.phone number already verified'),
                    ]);
                } else {
                     $sid = getenv("TWILIO_ACCOUNT_SID");
                     $token = getenv("TWILIO_AUTH_TOKEN");
                     $service = getenv("TWILIO_SERVICE_KEY");
                    $twilio = new Client($sid, $token);

                    if ($request->channel == "whatsapp") {
                        $verification = $twilio->verify->v2->services($service)
                            ->verifications
                            ->create($request->phone_number, "whatsapp");
                    }

                    if ($request->channel == "sms") {
                        $verification = $twilio->verify->v2->services($service)
                            ->verifications
                            ->create($request->phone_number, "sms");
                    }
                    if ($request->channel == "voice") {
                        $verification = $twilio->verify->v2->services($service)
                            ->verifications
                            ->create($request->phone_number, "call");
                    }

                    return response()->json([

                        'status' => "200",
                        "message" => __('auth.otp sent successfully to number'),
                        "status" => $verification->status,

                    ]);
                }
            } else {

                $otp =  Otp::create([
                    'phone_number' => $request->phone_number,
                ]);


                $sid = getenv("TWILIO_ACCOUNT_SID");
                     $token = getenv("TWILIO_AUTH_TOKEN");
                     $service = getenv("TWILIO_SERVICE_KEY");
                $twilio = new Client($sid, $token);

                if ($request->channel == "whatsapp") {
                    $verification = $twilio->verify->v2->services($service)
                        ->verifications
                        ->create($request->phone_number, "whatsapp");
                }

                if ($request->channel == "sms") {
                    $verification = $twilio->verify->v2->services($service)
                        ->verifications
                        ->create($request->phone_number, "sms");
                }
                if ($request->channel == "voice") {
                    $verification = $twilio->verify->v2->services($service)
                        ->verifications
                        ->create($request->phone_number, "call");
                }
                return response()->json([
                    'status' => "200",
                    "message" => __('otp sent successfully to number'),
                    "status" => $verification->status,
                ]);
            }
        }
        catch(\Exception $e) {
            return response()->json([
                'status'=>500,
                'message'=>$e->getMessage(),
            ]);
        }

    }

    public function otp_verification(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'phone_number' => 'required',
            'otp' => 'required',
        ]);
        $otp = Otp::where('phone_number', $request->phone_number)->first();
        // return $otp;
        if ($otp) {
            $sid = getenv("TWILIO_ACCOUNT_SID");
            $token = getenv("TWILIO_AUTH_TOKEN");
            $service = getenv("TWILIO_SERVICE_KEY");
            $twilio = new Client($sid, $token);

            $verification_check = $twilio->verify->v2->services($service)
                ->verificationChecks
                ->create(
                    [
                        "to" => $request->phone_number,
                        "code" => $request->otp
                    ]
                );

            if ($verification_check->status == "approved") {
                $otp->status = 1;
                $otp->save();
            }
            return response()->json([

                'status' => "200",
                "message" => $verification_check->status,

            ]);
        } else {
            return response()->json([

                'status' => "404",
                "message" => __('no account found against number'),

            ]);
        }
    }

    public function create_user_password(Request $request)
    {
        App::setLocale($request->locale);
        $check = Otp::where('phone_number', $request->phone_number)->value('status');
        if ($check == 1) {

            $user =  User::create([
                'phone_number' => $request->phone_number,
                'password' => \Hash::make($request->password),
            ]);

            if ($user) {
                return response()->json([
                    "status" => "200",
                    "message" => __('auth.user signup successfully'),
                    'user' => $user,
                ]);
            } else {
                return response()->json([
                    "status" => "204",
                    "message" => __('auth.signup error'),
                ]);
            }
        } else {
            return response()->json([

                'status' => "404",
                "message" => __('auth.phone number not verified'),

            ]);
        }
    }


    // public function social_login(Request $request){

    //     $user = "";
    //     $validated = $request->validate([
    //         'email' => 'required|unique:users',
    //         'token' => 'required'
    //     ]);

    //     if ($request->email == "" && $request->phone_number == "") {
    //         return response()->json([
    //             'status' => '422',
    //             'message' => 'please login through email'
    //         ]);
    //     }
    //     if ($request->phone_number) {
    //         $user = User::where('phone_number', $request->phone_number)->first();
    //     } elseif ($request->email) {
    //         $user = User::where('email', $request->email)->first();
    //     }

    //     if ($user) {
    //             return response()->json([
    //                 "status" => "200",
    //                 "message" => "user registered in the application! login through app cedentials",
    //             ]);

    //         }else {
    //             $user =  User::create([
    //                 'email' => $request->email,
    //                 'password' => \Hash::make($request->token),
    //             ]);
    //     if ($user) {
    //         return response()->json([
    //             "status" => "200",
    //             "message" => "user signin successfully",
    //             'token'=> $user->createToken('Laravel Password Grant Client')->accessToken,
    //             'user' => $user,
    //         ]);
    //                 }

    //         }
    // }

    public function social_login(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'nick_name' => 'required',
            'dob' => 'required',
            'fcm_token'=>'required'
        ]);

        $user_name =  "user" . "" . rand(1111111111, 9999999999);
        $request['user_name'] = $user_name;
        $request['nick_name'] = $request->nick_name;
        $request['profile_image'] = 'storage/user_image/aGXGaz2Rtcs3FpAMjDzuX1zIRBbDpK4im7DmdGqv.jpg';
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $flag = false;
            if (isset($request->apple_login_id)) {
                $user->update(['apple_login_id' => $request->apple_login_id]);
                $flag = true;
            } else if (isset($request->google_login_id)) {
                $user->update(['google_login_id' => $request->google_login_id]);
                $flag = true;
            }
            if (isset($request->facebook_login_id)) {
                $user->update(['facebook_login_id' => $request->facebook_login_id]);
                $flag = true;
            }
            if ($flag) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $user->update(['fcm_token'=>$request->fcm_token]);
                return response()->json([
                    "status" => "200",
                    "message" => __('auth.User loged in successfully'),
                    "user" => $user,
                    "token" => $token,
                ], 200);
            } else {
                return response()->json([
                    "status" => "401",
                    "message" => __('auth.Please provide a social auth token'),
                ], 401);
            }
        } else {

            $user = User::create($request->except('locale'));
            if ($user) {
                // $user->attachRole('user');
                $user = User::where('id', $user->id)->first();
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                return response()->json([
                    "status" => "200",
                    "message" => __('auth.User signed up successfully'),
                    "user" => $user,
                    "token" => $token,
                ], 200);
            }
        }
    }

    public function forgot_password(Request $request)
    {
            $request->validate([
                'phone_number' => 'required_without:email', // phone number or email address, only one is required
                'email' => 'required_without:phone_number',
                'channel' => 'required|in:whatsapp,sms,voice', // Ensure the channel is specified and valid
            ]);

            // Try to find the user by phone number first
            $user = User::where('phone_number', $request->get('phone_number'))->first();

            if ($user) {
                $sid = getenv("TWILIO_ACCOUNT_SID");
                $token = getenv("TWILIO_AUTH_TOKEN");
                $service = getenv("TWILIO_SERVICE_KEY");
                $twilio = new Client($sid, $token);

                // Determine the channel to use for verification
                $channel = $request->channel;
                $verificationServiceSid = $service; // Twilio verification service SID

                try {
                    $verification = $twilio->verify->v2->services($verificationServiceSid)
                        ->verifications
                        ->create($request->phone_number, $channel);

                    return response()->json([
                        'status' => "200",
                        'message' => __('auth.otp sent successfully to number'),
                        'verification_status' => $verification->status,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => "500",
                        'message' => __('auth.failed to send otp'),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // If user is not found by phone number, try by email
            $user_email = User::where('email', $request->get('email'))->first();

            if ($user_email) {
                // Send password reset email logic goes here
                // Assuming you have a function to send the password reset link
                try {
                    // Example function, replace with your actual email sending logic
                    Password::sendResetLink(['email' => $request->get('email')]);

                    return response()->json([
                        'status' => "200",
                        'message' => __('auth.reset link sent successfully to email'),
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => "500",
                        'message' => __('auth.failed to send reset link'),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // If user is not found by either phone number or email
            return response()->json([
                'status' => "404",
                'message' => __('auth.user not found'),
            ]);
    }


    public function reset_password(Request $request){
        $request->validate([
            'otp'=>'required',
            'phone_number'=>'required',
            'password'=>'required|confirmed',
        ]);
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $service = getenv("TWILIO_SERVICE_KEY");
        $twilio = new Client($sid, $token);

        $verification_check = $twilio->verify->v2->services($service)
            ->verificationChecks
            ->create(
                [
                    "to" => $request->phone_number,
                    "code" => $request->otp
                ]
            );

        if ($verification_check->status == "approved") {
            User::where('phone_number', $request->phone_number)->update([
                'password' => Hash::make($request->password),
            ]);
            return response()->json([

                'status' => "200",
                "message" => 'password updated successfully',

            ]);
        }
        else{
            return response()->json([

                'status' => "200",
                "message" => 'invalid otp',

            ]);
        }

    }
}
