<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\{User, Post,Report,Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
// use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\DefaultVideo;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\Gif;
use Intervention\Image\Facades\Image;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use App\Services\FCMService;
use App\Jobs\ProcessProfileVideo;
class UserController extends Controller
{
    private $fCMService;
    public function __construct(FCMService $fCMService)
    {
        $this->fCMService = $fCMService;
    }
    public function all_users(Request $request)
    {
        App::setLocale($request->locale);
        $users = User::inRandomOrder()->with('following', 'friend_added','blockedUsers')
            ->get()->except(auth()->id())
            ->map(function ($user) {
                $user->is_follow = auth()->user()->following && auth()->user()->following->contains($user->id) ? 1 : 0;
                // $user->is_follow = $user->following && $user->following->contains(auth()->id()) ? 1 : 0;
                $user->is_friend = $user->friend_added && $user->friend_added->contains(auth()->id()) ? 1 : 0;
                return $user->makeHidden('following', 'friend_added');
            });
        if ($users != "[]") {
            return response()->json([
                "status" => "200",
                "users" => $users,
            ]);
        } else {
            return response()->json([
                "status" => "404",
                "message" => __('no user found')
            ]);
        }
    }

    // public function follow_user(Request $request)
    // {
    //     App::setLocale($request->locale);
    //     $validated = $request->validate([
    //         'user_id' => 'required',
    //     ]);
    //     if ($request->user_id == auth()->user()->id) {
    //         return response()->json([
    //             "message" => __('auth.can not follow yourself'),
    //         ]);
    //     }
    //     if (auth()->user()->follow->contains($request->user_id)) {
    //         auth()->user()->follow()->detach($request->user_id);
    //         $deviceToken = User::where('id',$request->user_id)->first();
    //         $title ="Unfollow Notification";
    //         $body = auth()->user()->nick_name."unfollow's you";
    //         // $user_to_sent = User::find($request->user_id);
    //         $data = [
    //             'token'=>$deviceToken->fcm_token,
    //         ];
    //         $fcmtoken = $deviceToken->fcm_token;
    //         Notification::create([
    //             'user_id'=>$deviceToken->id,
    //             'user'=>auth()->user(),
    //             'channel_name'=>"user unfollowed",
    //             'token'=>$fcmtoken,
    //         ]);
    //         try{
    //             if($fcmtoken){
    //                 $this->fCMService->sendNotification($fcmtoken, $title, $body,$data);
    //             }
    //         }
    //         catch(\Exception $e){
    //             // return response()->json([
    //             //     'status'=>500,
    //             //     'message'=>$e->getMessage()
    //             // ]);
    //             return response()->json([
    //                 "status" => 200,
    //                 "message" => __('auth.user follow successfully!'),
    //             ], 200);
    //         }
    //     } else {
    //         auth()->user()->follow()->attach($request->user_id);

    //         $deviceToken = User::where('id',$request->user_id)->first();
    //         $title ="Follow Notification";
    //         $body = auth()->user()->nick_name." follow's you";
    //         // $user_to_sent = User::find($request->user_id);
    //         $data = [
    //             'token'=>$deviceToken->fcm_token,
    //         ];
    //         $fcmtoken = $deviceToken->fcm_token;
    //         Notification::create([
    //             'user_id'=>$deviceToken->id,
    //             'user'=>auth()->user(),
    //             'channel_name'=>"user followed",
    //             'token'=>$fcmtoken,
    //         ]);
    //         try{
    //             if($fcmtoken){
    //                 $this->fCMService->sendNotification($fcmtoken, $title, $body,$data);
    //             }
    //         }
    //         catch(\Exception $e){
    //             // return response()->json([
    //             //     'status'=>500,
    //             //     'message'=>$e->getMessage()
    //             // ]);
    //             return response()->json([
    //                 "status" => 200,
    //                 "message" => __('auth.user follow successfully!'),
    //             ], 200);
    //         }
    //         return response()->json([
    //             "status" => 200,
    //             "message" => __('auth.user follow successfully!'),
    //         ], 200);
    //     }
    // }

    public function follow_user(Request $request)
{
    // Set the locale
    App::setLocale($request->locale);

    // Validate request
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id', // Added exists rule for better validation
    ]);

    $currentUser = auth()->user();
    $followUserId = $request->user_id;

    // Prevent user from following themselves
    if ($followUserId == $currentUser->id) {
        return response()->json([
            "message" => __('auth.can not follow yourself'),
        ], 400);
    }

    // Check if user is already followed
    $isFollowing = $currentUser->follow->contains($followUserId);

    // Toggle follow/unfollow
    if ($isFollowing) {
        $currentUser->follow()->detach($followUserId);
        $notificationChannel = "user unfollowed";
        $title = "Unfollow Notification";
        $body = $currentUser->nick_name . " unfollowed you";
    } else {
        $currentUser->follow()->attach($followUserId);
        $notificationChannel = "user followed";
        $title = "Follow Notification";
        $body = $currentUser->nick_name . " followed you";
    }

    // Fetch device token of the user being followed/unfollowed
    $deviceToken = User::find($followUserId)->fcm_token;

    // Create notification
    Notification::create([
        'user_id' => $followUserId,
        'user' => $currentUser,
        'channel_name' => $notificationChannel,
        'token' => $deviceToken,
    ]);

    // Send FCM Notification
    if ($deviceToken) {
        try {
            $this->fCMService->sendNotification($deviceToken, $title, $body, ['token' => $deviceToken]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    // Response
    return response()->json([
        "status" => 200,
        "message" => __('auth.user follow status changed successfully!'),
    ], 200);
    }


    public function get_user(Request $request)
    {
        App::setLocale($request->locale);
        $user = "";
        $total_likes = 0;

        try {
            if (isset($request->user_id)) {
                $user = User::where('id', $request->user_id)->withCount('follow', 'following','friend_added','blockedUsers')->with('post', 'like_post', 'favourites','blockedUsers')->first();
                $liked_posts = Post::where('user_id', $request->user_id)->withCount('like_post_user')
                ->get()
                ->map(function ($liked_post) {
                    $liked_post->is_liked = $liked_post->like_post_user && $liked_post->like_post_user->contains(auth()->id()) ? 1 : 0;
                    return $liked_post;
                });
                $total_likes = $liked_posts->sum('like_post_user_count');
            } else {
                $user = User::where('id', auth()->user()->id)->withCount('follow', 'following','friend_added','blockedUsers')->with('post', 'like_post', 'favourites','blockedUsers')->first();
                $liked_posts = Post::where('user_id', auth()->id())->withCount('like_post_user')->get()
                ->map(function ($liked_post) {
                    $liked_post->is_liked = $liked_post->like_post_user && $liked_post->like_post_user->contains(auth()->id()) ? 1 : 0;
                    return $liked_post;
                });
                $total_likes = $liked_posts->sum('like_post_user_count');
            }
            if($user){
                $user->is_follow = $user->following && $user->following->contains(auth()->id()) ? 1 : 0;
                $user->is_friend = $user->friend_added && $user->friend_added->contains(auth()->id()) ? 1 : 0;
                $user->is_blocked = $user->blockedUsers && $user->blockedUsers->contains(auth()->id()) ? 1 : 0; // Check if the user is blocked by the current user


                // $liked_posts = $liked_posts->map(function ($post) {
                //     $post->total_like_post_count = $post->like_post_user_count;
                //     return $post;
                // });
                return response()->json([
                    'user' => $user,
                    // 'total_likes' => ($liked_posts->count()) ? $liked_posts[0]->total_like_post_count : 0,
                    'total_likes'=>$total_likes
                ]);
            }
            else{
                return response()->json([
                    'message' => __('auth.user not found'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function get_interests()
    {
        $interests = Interest::all();
        return response()->json([
            'status' => '200',
            'interests' => $interests,
        ]);
    }

    public function save_user_interests(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'interest_id' => 'required',
        ]);

        auth()->user()->interest()->attach(json_decode($request->interest_id));
        return response()->json([
            "status" => 200,
            "message" => __('auth.user interests attach successfully!'),
        ], 200);
    }
    public function find_friends_in_contact_list(Request $request)
    {
        App::setLocale($request->locale);
        $normalizedContacts = $request->phone_numbers;
        $contacts = User::where(function ($query) use ($normalizedContacts) {
            $query->whereIn('phone_number', $normalizedContacts)
                ->orWhere(function ($subquery) use ($normalizedContacts) {
                    foreach ($normalizedContacts as $phoneNumber) {
                        $lastSixDigits = substr($phoneNumber, -6);
                        $subquery->orWhere(DB::raw("SUBSTRING(phone_number, -6)"), $lastSixDigits);
                    }
                });
        })->get();
        if ($contacts != "[]") {
            return response()->json([
                "status" => 200,
                "contacts" => $contacts,
            ], 200);
        } else {
            return response()->json([
                "status" => 200,
                "contacts" => __('auth.no contact found'),
            ], 200);
        }
    }


    public function add_friend(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'user_id' => 'required',
        ]);
        if (auth()->user()->id != $request->user_id) {
            if (auth()->user()->friend_requests_sent->contains($request->user_id)) {
                auth()->user()->friend_requests_sent()->detach($request->user_id);
                return response()->json([
                    "status" => 200,
                    "message" => __('auth.friend request unsent successfully!'),
                ], 200);
            } else {
                auth()->user()->friend_requests_sent()->attach($request->user_id);
                return response()->json([
                    "status" => 200,
                    "message" => __('auth.friend request sent successfully!'),
                ], 200);
            }
        } else {
            return response()->json([
                "status" => 202,
                "message" => __('auth.can not send request to one self'),
            ], 200);
        }
    }


    public function show_friend_requests(Request $request)
    {
        App::setLocale($request->locale);
        // $requests = DB::table('friend_requests')->where('user_id', auth()->user()->id)->join('users', 'friend_requests.user_id', 'users.id')->get();
        $user = User::where('id', auth()->user()->id)->with('friend_requests_recieve')->first();
        // return response()->json([
        //     "status" => 202,
        //     "requests" => $requests,
        // ], 200);
        return $user->friend_requests_recieve;
    }

    public function accept_reject_friend_request(Request $request)
    {
        App::setLocale($request->locale);
        // $friend_request_data = DB::Table('friend_requests')->where('friend_id', $request->friend_id)->where('user_id', auth()->user()->id)->first();
        $validated = $request->validate([
            'friend_id' => 'required',
        ]);
        $user = User::where('id', $request->friend_id)->first();
        if (auth()->user()->id != $request->friend_id) {
            if (auth()->user()->friend_requests_sent->contains($request->friend_id) && $request->status == "reject") {
                $user->friend_requests_sent()->detach(auth()->user()->id);
                return response()->json([
                    "status" => 200,
                    "message" => __('auth.friend request rejected successfully!'),
                ], 200);
            } else {

                auth()->user()->friend_added()->attach($request->friend_id);


                $user->friend_added()->attach(auth()->user()->id);

                $user->friend_requests_sent()->detach(auth()->user()->id);
                return response()->json([
                    "status" => 200,
                    "message" => __('auth.added as friend successfully!'),
                ], 200);
            }
        } else {
            return response()->json([
                "status" => 202,
                "message" => __('auth.can not add friend to one self'),
            ], 200);
        }
    }

    public function show_friends(Request $request)
    {
        $friends = User::where('id', auth()->user()->id)->with('friend_added','blockedUsers')->first();
        return $friends->friend_added;
    }

    public function get_user_posts(Request $request)
    {
        App::setLocale($request->locale);
        $posts = Post::where('user_id', auth()->id())->withCount('comments', 'like_post_user', 'favourites', 'hash_tags')
        ->with('comments', 'user')
        ->get()
        ->map(function ($post) {
            $post->is_liked = $post->like_post_user && $post->like_post_user->contains(auth()->id()) ? 1 : 0;
            return $post;
        });
        if (count($posts)) {
            return $posts;
        } else {
            return response()->json([
                'status' => 404,
                'message' => __('auth.no data found'),
            ]);
        }
    }

    // public function edit_profile(Request $request)
    // {
    //     $request->validate([
    //         'profile_video' => 'required|file|max:10240', // 10240 KB = 10 MB
    //     ], [
    //         'profile_video.max' => 'The video size must be less than 10MB.',
    //     ]);

    //     App::setLocale($request->locale);
    //     $user = User::where('id', auth()->user()->id)->first();

    //     if ($request->nick_name && $request->nick_name != null) {
    //         $user->nick_name = $request->nick_name;
    //     }
    //     // if($request->email && $request->email != null){
    //     //     $user->email = $request->email;
    //     // }
    //     if ($request->phone_number && $request->phone_number != null) {
    //         $user->phone_number = $request->phone_number;
    //     }
    //     if ($request->bio && $request->bio != null) {
    //         $user->bio = $request->bio;
    //     }
    //     if ($request->hasFile('profile_image')) {
    //         $image = $request->file('profile_image');
    //         $file_path = Storage::disk('s3')->put('public/user_image', $image);
    //         // $file_path = str_replace("public", "storage", $file_path);
    //         $file_path='https://d1s3gnygbw6wyo.cloudfront.net'.$file_path;
    //         $user->profile_image = $file_path;
    //     }
    //     if ($request->hasFile('profile_video')) {
    //         // return 1;
    //         $video = $request->file('profile_video');

    //         $file_path = Storage::put('public/user_video', $video);
    //         // $file_path = str_replace("public/", "", $file_path);
    //         $user->profile_image = $file_path;

    //         $user->profile_video = str_replace("public", "storage", $file_path);
    //         $videoPath = $file_path;

    //         // $videoPath = 'path/to/video.mp4';
    //         $outputPath = '/public/user_gifs/'.uniqid().'.gif';


    //         FFMpeg::fromDisk('local')
    //         ->open($videoPath)
    //         ->addFilterAsComplexFilter(
    //             ['-ss 0', '-t 3'],
    //             [
    //                 '-vf "fps=10,scale=360:-1:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse"',
    //                 '-loop 0',
    //             ]
    //         )
    //         ->export()
    //         ->toDisk('s3')
    //         ->save(
    //             $outputPath
    //         );

    //        $user->profile_image = 'https://d1s3gnygbw6wyo.cloudfront.net'.$outputPath;
    //     //    unlink(str_replace("public", "storage", $videoPath));
    //     }
    //     $user->save();
    //     return response()->json([
    //         'status' => 200,
    //         'message' => __('auth.profile updated successfully'),
    //         'user'=>$user,
    //     ]);
    // }


    public function edit_profile(Request $request)
    {
        $request->validate([
            'profile_video' => 'sometimes|nullable|file|max:10240', // 10 MB
        ], [
            'profile_video.max' => 'The video size must be less than 10MB.',
        ]);

        App::setLocale($request->locale);
        $user = auth()->user();

        // Update non-empty fields
        $user->fill($request->only(['nick_name', 'phone_number', 'bio']));

        // Process and upload profile image if provided
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $file_path = Storage::disk('s3')->put('public/user_image', $image);
            $user->profile_image = 'https://d1s3gnygbw6wyo.cloudfront.net' . $file_path;
        }

        // Process and upload profile video if provided
        if ($request->hasFile('profile_video')) {
            $video = $request->file('profile_video');
            $videoPath = Storage::disk('s3')->put('public/user_video', $video);
            $outputPath = 'public/user_gifs/' . uniqid() . '.gif';

            // Dispatch the job to handle video processing
            ProcessProfileVideo::dispatch($videoPath, $outputPath, $user);
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'message' => __('auth.profile updated successfully'),
            'user' => $user,
        ]);
    }




    public function convertToGif($videoPath, $outputPath, $startTime, $duration)
    {
        \FFMpeg\FFProbe::create([
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);

        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => '/usr/bin/ffmpeg', // Replace with the correct path to ffmpeg
            'ffprobe.binaries' => '/usr/bin/ffprobe', // Replace with the correct path to ffprobe
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);
        $video = $ffmpeg->open($videoPath);

        $video->filters()
        ->clip(\FFMpeg\Coordinate\TimeCode::fromSeconds($startTime), \FFMpeg\Coordinate\TimeCode::fromSeconds($duration));

        $format = new DefaultVideo();

        // Save the video clip
        $video->save($format, $outputPath);

        // Convert the saved video to GIF using the Intervention Image library
        $image = Image::make($outputPath);
        $image->save($outputPath.'.gif');

        return response()->download($outputPath.'.gif')->deleteFileAfterSend(true);

    }

    public function report_user_post(Request $request){

        $report = Report::create([
            'reported_id'=>$request->report_to,
            'report_by'=>auth()->user()->id,
            'post_id'=>$request->post_id,
            'description'=>$request->reason,
        ]);

        if($report){
            return response()->json([
              'status' => 200,
              'message' => __('auth.reported successfully'),
            ]);
        }

    }

    public function delete_account(Request $request){
        $user = auth()->user()->delete();
        return response()->json([
            'status'=>200,
            'message'=>"account deleted"
        ]);
    }

    public function block_user(Request $request){
        $request->validate([
            'user_id'=>'required|exists:users,id'
        ]);
    $blockUserId = $request->input('user_id');
    // $blockUserId = $request->input('block_user_id');

    $user = auth()->user();
    $blockUser = User::findOrFail($blockUserId);

    // Check if the user is already blocked
    if ($user->blockedUsers()->where('id', $blockUserId)->exists()) {
        // If already blocked, unblock the user
        $user->blockedUsers()->detach($blockUserId);
        return response()->json(['message' => 'User unblocked successfully'], 200);
    }

    // If not blocked, block the user
    $user->blockedUsers()->attach($blockUserId);

    return response()->json(['message' => 'User blocked successfully'], 200);
    }

    public function get_all_blocked_users(){
        $users = auth()->user()->blockedUsers()->get();
        return response()->json([
            'status'=>200,
            'blocked users'=>$users,
        ]);
    }
}
