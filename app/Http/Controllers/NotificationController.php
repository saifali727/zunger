<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
class NotificationController extends Controller
{
    public function send_fcm_notification(Request $request){
        $tokens = User::where('fcm_token','!=',null)->get();
        $title = $request->input('title');
        $body = $request->input('body');
        foreach($tokens as $token){

        }
        $serverKey = config('services.fcm.server_key');
        $url = 'https://fcm.googleapis.com/fcm/send';

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        return $response->json();
    }
}
