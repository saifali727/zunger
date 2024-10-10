<?php

namespace App\Http\Controllers;

use App\Models\{User,Notification};
use Illuminate\Http\Request;
use App\Services\FCMService;
use Illuminate\Support\Facades\Http;

class FcmController extends Controller
{
    private $fCMService;
    public function __construct(FCMService $fCMService)
    {
        $this->fCMService = $fCMService;
    }
    public function send_fcm_notification(Request $request){
        $request->validate([
            'token'=>'required',
            // 'user_id'=>'required|exists:users,id',
            'channel_name'=>'required',
        ]);
        $deviceToken = User::where('id',auth()->user()->id)->first()->load('followedUsers'); // Get device token from request
        // return $deviceTokens;
        $title ="your friend ".$deviceToken->nick_name." has live on channel ".$request->channel_name;
        $body = "click to join the live video streaming";
        if($deviceToken->followedUsers){
            $errors = [];

            foreach($deviceToken->followedUsers as $token){
                $fcmtoken = $token->fcm_token;
                $user_to_sent = User::find($token->id);
                $data = [
                    'token' => $request->token,
                    'user' => $user_to_sent,
                    'channel_name' => $request->channel_name
                ];

                Notification::create([
                    'user_id' => $token->id,
                    'user' => auth()->user(),
                    'channel_name' => $request->channel_name,
                    'token' => $request->token,
                ]);

                try {
                    if ($fcmtoken) {
                        $this->fCMService->sendNotification($fcmtoken, $title, $body, $data);
                    }
                } catch (\Exception $e) {
                    // Collect errors instead of breaking the loop
                    $errors[] = [
                        'user_id' => $token->id,
                        'message' => $e->getMessage()
                    ];
                }
            }

            // After the loop, return a response
            if (!empty($errors)) {
                // return response()->json([
                //     'status' => 500,
                //     'message' => 'Some notifications failed to send',
                //     'errors' => $errors,
                // ]);
                return response()->json([
                    'status'=>200,
                    'message'=>'Notifications sent successfully',
                    'data'=>$deviceToken,
                ]);
            } else {
                return response()->json([
                    'status'=>200,
                    'message'=>'Notifications sent successfully',
                    'data'=>$deviceToken,
                ]);
            }
        }
        else{
            return response()->json([
                'status'=>200,
                'message'=>'no followers',
            ]);
        }



    }

    public function get_notification(Request $request){
        $notifications = Notification::where('user_id',auth()->user()->id)->get();
        return response()->json([
            'status'=>200,
            'notifications'=>$notifications
        ]);
    }

}
