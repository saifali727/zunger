<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HashTag;
use App\Models\Post;
use App\Models\{User,Notification};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use PDO;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;
use FFMpeg\Coordinate\TimeCode;
use function PHPSTORM_META\map;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Exception\InvalidArgumentException;
use App\Services\FCMService;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessVideoUpload;

class PostController extends Controller
{
    private $fCMService;
    public function __construct(FCMService $fCMService)
    {
        $this->fCMService = $fCMService;
    }
    // public function upload_video(Request $request)
    // {
    //     // return $request->all();
    //     App::setLocale($request->locale);

    //     $mention_array = [];
    //     $hashtags_array = [];
    //     $hashtag_index = 0;
    //     $mention_index = 0;
    //     $description = "";
    //     $request['user_id'] = auth()->user()->id;
    //     $request['type'] = "post";
    //     $audio_url = "";
    //     $video_file = "";
    //     $disk = $request->audio_link? "public": "s3";
    //     try {
    //         $post = Post::create($request->except('file', 'thumbnail', 'description', 'people_tags','audio_link','file_type','link_type'));
    //         if ($request->hasfile('file')) {
    //             $file = $request->file('file');
    //             $extension = $file->getClientOriginalExtension();
    //             // $filename = rand(1111, 9999) . "" . time() . "." . $extension;
    //             $url =  Storage::disk($disk)->put("zunger/users/videos", $file);
    //             if($disk == "public"){
    //             //    $url = $url;
    //                $video_file = $url;
    //             }
    //             else{
    //                 // $url = Storage::disk($disk)->url($url);
    //                 $video_file = 'https://d1s3gnygbw6wyo.cloudfront.net/'.$url;
    //                 // $video_file = str_replace("https://zunger321.s3.amazonaws.com", "https://duai0zal0fg0e.cloudfront.net", $video_file);
    //             }

    //             $post->update(['url' => $video_file]);
    //             // sleep(10);
    //         }
    //         if ($request->hasfile('thumbnail')) {
    //             $file = $request->file('thumbnail');
    //             $extension = $file->getClientOriginalExtension();
    //             $filename = rand(1111, 9999) . "" . time() . "." . $extension;
    //             $url =  Storage::disk('s3')->put("public/uploads/thumbnails", $file);
    //             // $url = Storage::disk($disk)->url($url);
    //             $post->update(['thumbnail' => 'https://d1s3gnygbw6wyo.cloudfront.net/'.$url]);
    //         }
    //         if($request->audio_link){
    //             if(isset($request->link_type)){
    //                 $externalAudioUrl = $request->audio_link;// Replace with the actual external audio URL
    //                 $client = new Client();
    //                 $response = $client->get($externalAudioUrl);
    //                 $audioData = $response->getBody()->getContents();
    //                 $tempAudioFile = 'temp_' . uniqid() . '.mp3';
    //                 $path = "public/uploads/audios/";
    //                 $success = Storage::disk($disk)->put($path.$tempAudioFile, $audioData);
    //                 if ($success) {
    //                     // Get the URL of the stored file
    //                     $audio_url = $path.$tempAudioFile;
    //                     // return $audio_url;
    //                     // Do whatever you need to do with $audio_url
    //                     // For example, return it in a response
    //                 } else {
    //                     // Handle the case where storing the file failed
    //                     // For example, return an error response
    //                     return response()->json(['error' => 'Failed to store the audio file'], 500);
    //                 }
    //             }
    //             if(isset($request->file_type)){
    //                     $tempAudioFile = 'temp_' . uniqid() . '.mp3';
    //                     $path = "public/uploads/audios";

    //                     // Assuming $request->file('audio_link') returns the uploaded file
    //                     $uploadedFile = $request->file('audio_link');

    //                     // Store the uploaded file using Laravel's Storage facade
    //                     $storedFilePath = $uploadedFile->storeAs($path, $tempAudioFile, 'public');

    //                     if($storedFilePath) {
    //                         // $audio_url = $path . $tempAudioFile;
    //                         $audio_url = $storedFilePath;
    //                         // Now you can save $audio_url to your database or use it as needed
    //                     }
    //             }

    //             $outputPath = 'zunger/users/videos'.uniqid().'.mp4';
    //                 // return $audio_url;
    //             try {
    //                 FFMpeg::fromDisk('public')
    //                 ->open([$video_file, $audio_url])
    //                 ->export()
    //                 ->addFormatOutputMapping(new X264, Media::make('s3', $outputPath), ['0:v', '1:a'])
    //                 ->save();

    //                 // return $outputPath; // Return the path to the output video
    //                 $post->update(['url'=>'https://d1s3gnygbw6wyo.cloudfront.net/'.$outputPath]);
    //             }
    //             catch(\Exception $e){
    //                 return $e->getMessage();
    //             }

    //         }
    //         $description_array = explode(" ", $request->description);
    //         foreach ($description_array as $word) {
    //             if ($word[0] == "#") {
    //                 $hashtags_array[$hashtag_index] = $word;
    //                 $hashtag_index += 1;
    //             } elseif ($word[0] == "@") {
    //                 $mention_array[$mention_index] = str_replace("@", "", $word);
    //                 $mention_index += 1;
    //             } else {
    //                 $description = $description . " " . $word;
    //             }
    //         }

    //         foreach ($hashtags_array as $hash) {
    //             $hash_tag = HashTag::where('title', $hash)->first();
    //             if (!$hash_tag) {
    //                 $hash_tag = HashTag::create([
    //                     'title' => $hash,
    //                 ]);
    //             }
    //             $hash_tag->posts()->attach($post->id);
    //         }

    //         $user_ids = User::whereIn('user_name', $mention_array)->get()->pluck('id')->toArray();
    //         $post->tags()->attach(json_decode($request->people_tags));
    //         $post->mention_posts()->attach($user_ids);
    //         $post =  $post->update(['description' => $description]);
    //         if ($post) {
    //             return response()->json([
    //                 'status' => "200",
    //                 'message' => __('auth.video posted successfully'),
    //                 'data' => $post,
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         return $e->getMessage();
    //     }
    // }


public function upload_video(Request $request)
{
    App::setLocale($request->locale);

    $request['user_id'] = auth()->user()->id;
    $request['type'] = "post";
    $mention_array = [];
    $hashtags_array = [];
    $disk = $request->audio_link ? "public" : "s3";
    $video_file = "";
    $audio_url = "";

    try {
        // Create post without media fields
        $post = Post::create($request->except('file', 'thumbnail', 'description', 'people_tags', 'audio_link', 'file_type', 'link_type'));

        // Upload video and thumbnail asynchronously
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $video_file = $file->store("zunger/users/videos", $disk);
            $post->url = $disk == "s3" ? 'https://d1s3gnygbw6wyo.cloudfront.net/' . $video_file : $video_file;
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnail_url = $thumbnail->store("public/uploads/thumbnails", 's3');
            $post->thumbnail = 'https://d1s3gnygbw6wyo.cloudfront.net/' . $thumbnail_url;
        }

        // Handle audio file upload
        if ($request->audio_link) {
            $audio_url = $this->handleAudioUpload($request, $disk);
        }

        // Queue FFMpeg processing if both video and audio are available
        if ($video_file) {
            ProcessVideoUpload::dispatch($post, $video_file, $audio_url, $disk);
        }


        // Extract and handle hashtags and mentions
        $this->processDescription($request->description, $post);

        // Batch update post data
        $post->update([
            'description' => $request->description,
            'url' => $post->url ?? null,
            'thumbnail' => $post->thumbnail ?? null,
        ]);

        return response()->json([
            'status' => "200",
            'message' => __('auth.video posted successfully'),
            'data' => $post,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function handleAudioUpload(Request $request, $disk)
{
    $audio_url = "";
    if ($request->link_type) {
        $client = new Client();
        $audioData = $client->get($request->audio_link)->getBody()->getContents();
        $audio_url = 'public/uploads/audios/temp_' . uniqid() . '.mp3';
        Storage::disk($disk)->put($audio_url, $audioData);
    } elseif ($request->file_type) {
        $uploadedFile = $request->file('audio_link');
        $audio_url = $uploadedFile->storeAs('public/uploads/audios', 'temp_' . uniqid() . '.mp3', 'public');
    }
    return $audio_url;
}

private function processDescription($description, $post)
{
    $mention_array = [];
    $hashtags_array = [];
    $description_words = explode(" ", $description);

    foreach ($description_words as $word) {
        if ($word[0] == "#") {
            $hashtags_array[] = $word;
        } elseif ($word[0] == "@") {
            $mention_array[] = str_replace("@", "", $word);
        }
    }

    // Attach hashtags
    foreach ($hashtags_array as $hash) {
        $hash_tag = HashTag::firstOrCreate(['title' => $hash]);
        $hash_tag->posts()->attach($post->id);
    }

    // Attach mentions
    $user_ids = User::whereIn('user_name', $mention_array)->pluck('id')->toArray();
    $post->mention_posts()->attach($user_ids);
}

    public function upload_story(Request $request)
    {
        App::setLocale($request->locale);
        $request->validate([
            'file' => 'required|max:20240', // 20MB max size
        ]);

        $mention_array = [];
        $hashtags_array = [];
        $hashtag_index = 0;
        $mention_index = 0;
        $description = "";
        $request['user_id'] = auth()->user()->id;
        $request['type'] = "story";
        try {
            $post = Post::create($request->except('file', 'description', 'people_tags'));
            if ($request->hasfile('file')) {

                // $s3Client = new S3Client([
                //     'version' => 'latest',
                //     'region' => 'your_aws_region',
                //     'credentials' => [
                //         'key' => 'your_aws_access_key',
                //         'secret' => 'your_aws_secret_key',
                //     ]
                // ]);

                // $bucket = 'your_s3_bucket_name';

                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = rand(1111, 9999) . "" . time() . "." . $extension;



                $url =  Storage::disk('s3')->put("public/uploads", $file);
                $post->update(['url' => $url]);
            }

            $description_array = explode(" ", $request->description);
            foreach ($description_array as $word) {
                if ($word[0] == "#") {
                    $hashtags_array[$hashtag_index] = $word;
                    $hashtag_index += 1;
                } elseif ($word[0] == "@") {
                    $mention_array[$mention_index] = str_replace("@", "", $word);
                    $mention_index += 1;
                } else {
                    $description = $description . " " . $word;
                }
            }

            foreach ($hashtags_array as $hash) {
                $hash_tag = HashTag::where('title', $hash)->first();
                if (!$hash_tag) {
                    $hash_tag = HashTag::create([
                        'title' => $hash,
                    ]);
                }
                $hash_tag->posts()->attach($post->id);
            }

            $user_ids = User::whereIn('user_name', $mention_array)->get()->pluck('id')->toArray();

            $post->tags()->attach(json_decode($request->people_tags));
            $post->mention_posts()->attach($user_ids);
            $post =  $post->update(['description' => $description]);
            if ($post) {
                return response()->json([
                    'status' => "200",
                    'message' => __('auth.video posted successfully'),
                    'data' => $post,
                ]);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function like_unlike_post(Request $request)
    {
        App::setLocale($request->locale);

        $validated = $request->validate([
            'post_id' => 'required',
        ]);

        $post = Post::find($request->post_id);

        if ($post) {
            // Check if the authenticated user is the owner of the post
            if (auth()->user()->id == $post->user_id) {
                return response()->json([
                    "status" => 403,
                    "message" => __('auth.You cannot like or unlike your own post!'),
                ], 403);
            }

            if (auth()->user()->like_post->contains($request->post_id)) {
                auth()->user()->like_post()->detach($request->post_id);

                $deviceToken = User::where('id', $post->user_id)->first();
                $title = auth()->user()->nick_name . " has unliked your post";
                $body = "click to watch a video";
                $data = [
                    'token' => $deviceToken->fcm_token,
                ];
                $fcmtoken = $deviceToken->fcm_token;

                Notification::create([
                    'user_id' => $deviceToken->id,
                    'user' => auth()->user()->id,
                    'channel_name' => 'post unlike',
                    'token' => $fcmtoken,
                ]);

                try {
                    if ($fcmtoken) {
                        $this->fCMService->sendNotification($fcmtoken, $title, $body, $data);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 500,
                        'message' => $e->getMessage()
                    ]);
                }

                return response()->json([
                    "status" => 200,
                    "message" => __('auth.post unliked successfully!'),
                ], 200);
            } else {
                auth()->user()->like_post()->attach($request->post_id);

                $deviceToken = User::where('id', $post->user_id)->first();
                $title = auth()->user()->nick_name . " has liked your post";
                $body = "click to watch a video";
                $data = [
                    'token' => $deviceToken->fcm_token,
                ];
                $fcmtoken = $deviceToken->fcm_token;

                Notification::create([
                    'user_id' => $deviceToken->id,
                    'user' => auth()->user(),
                    'channel_name' => 'post like',
                    'token' => $fcmtoken,
                ]);

                try {
                    if ($fcmtoken) {
                        $this->fCMService->sendNotification($fcmtoken, $title, $body, $data);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 500,
                        'message' => $e->getMessage()
                    ]);
                }

                return response()->json([
                    "status" => 200,
                    "message" => __('auth.post liked successfully!'),
                ], 200);
            }
        } else {
            return response()->json([
                "status" => 404,
                "message" => __('auth.No post found!'),
            ], 404);
        }
    }

    public function favourite_unfavourite_post(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'post_id' => 'required',
        ]);
        if (auth()->user()->favourites->contains($request->post_id)) {
            auth()->user()->favourites()->detach($request->post_id);
            return response()->json([
                "status" => 200,
                "message" => __('auth.post unfavourite successfully!'),
            ], 200);
        } else {
            auth()->user()->favourites()->attach($request->post_id);
            return response()->json([
                "status" => 200,
                "message" => __('auth.post favourite successfully!'),
            ], 200);
        }
    }

    public function share_post(Request $request)
    {
        App::setLocale($request->locale);
        $post =  Post::where('id', $request->post_id)->first();
        $post->update([
            'share_count' => $post->share_count + 1,
        ]);
        $post->save();
        return response()->json([
            'status' => 200,
            'message' => __('auth.shared successfully'),
        ]);
    }

    public function get_friends_posts(Request $request)
    {
        App::setLocale($request->locale);
        $authenticatedUser = auth()->user();
        $followedUserIds = $authenticatedUser->friend_added()->pluck('users.id');

        $posts = Post::whereIn('user_id', $followedUserIds)->with('comments', 'user')->withCount('comments', 'like_post_user', 'favourites', 'hash_tags')->get();

        $posts = $posts->map(function ($post) use ($authenticatedUser) {
            $post->is_liked = $authenticatedUser->likedPosts && $authenticatedUser->likedPosts->contains($post) ? 1 : 0;
            return $post;
        });

        // Return the posts or pass them to the view
        return response()->json($posts);
    }

    public function delete_post(Request $request)
    {
        App::setLocale($request->locale);
        $post = Post::where('id', $request->post_id)->delete();
        if ($post) {
            return response()->json([
                'status' => 200,
                'message' => __('auth.post deleted successfully'),
            ]);
        }
        else{
            return response()->json([
                'status'=>200,
                'message' => __('auth.no data found'),
            ]);
        }
    }

    public function addBackgroundMusicToVideo()
    {
        $videoPath = public_path('input_video.mp4');
        $backgroundMusicPath = public_path('background_music.mp3');
        $outputPath = public_path('output_video.mp4');

        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open($videoPath);

        // Prepare the background music
        $audio = $ffmpeg->open($backgroundMusicPath);

        // Set the start time for the background music (optional)
        $startTime = 0; // in seconds

        // Add the background music to the video starting from the specified time
        $video->addAudio($audio, function ($audio) use ($startTime) {
            $audio->setStartTime(TimeCode::fromSeconds($startTime));
        });

        // Save the video with the background music
        $format = new \FFMpeg\Format\Video\X264();
        $video->save($format, $outputPath);

        // Return the path to the output video file
        return $outputPath;
    }

    public function make_duet(Request $request){
        // ini_set('max_execution_time', 60000);
        $validated = $request->validate([
            'post_id'=>'required',
            'video'=>'required',
        ]);
        $url2="";
        $url3="";
        $video1 = "";
        try {
        if ($request->post_id) {
            $video1 = Post::find($request->post_id);
            // if($video1->duet_status == 1){
            //   $url1 = $video1->url;
            //   $url1 = str_replace('storage', 'public' , $url1);
            // //   $url3='public/uploads/'.uniqid().'.mp4';
            // }
            // else{
            //     return response()->json([
            //         'status'=>401,
            //         'message'>'duet with the video not allowed',
            //     ]);
            // }

        }
        if ($request->hasfile('video')) {
            $file = $request->file('video');
            $extension = $file->getClientOriginalExtension();
            $url2 =  Storage::disk('s3')->put("public/uploads", $file);
            // $url2 = Storage::disk('s3')->url($url2);
            $url2 = 'https://d1s3gnygbw6wyo.cloudfront.net'.$url2;
        }
        if ($request->hasfile('thumbnail')) {
            $file = $request->file('thumbnail');
            $extension = $file->getClientOriginalExtension();
            $filename = rand(1111, 9999) . "" . time() . "." . $extension;
            $url3 =  Storage::disk('s3')->put("public/uploads/thumbnails", $file);
            // $url3 = Storage::disk('s3')->url($url3);
            $url3 = 'https://d1s3gnygbw6wyo.cloudfront.net'.$url3;
        }

            // First FFMpeg operation
            // FFMpeg::open($url2)
            // ->export()
            // ->inFormat(new \FFMpeg\Format\Video\X264)
            // ->resize(640, 480)
            // ->save($url4);

        // return $url4;
            // $outputPath = 'public/uploads/'.uniqid().'.mp4';
            //     FFMpeg::fromDisk('local')
            //         ->open([$url1, $url4])
            //         ->export()
            //         ->addFilter('[0:v][1:v]', 'hstack', '[v]')
            //         ->addFormatOutputMapping(new X264, Media::make('local', $outputPath), ['0:a?', '[v]'])
            //         ->save();


                // Check if the second operation was successful
                    // $outputPath = str_replace('public', 'storage', $outputPath);
                    $post = Post::create([
                        'user_id'=>auth()->user()->id,
                        'url'=>$url2,
                        'duet_with'=>$video1->url,
                        'is_duet'=>1,
                        'type'=>'post',
                        'is_private'=>0,
                        'watch_status'=>'Everyone',
                        'comment_status'=>1,
                        'duet_status'=>1,
                        'stitch_status'=>1,
                        'quality_uploads'=>1,
                        'thumbnail'=>$url3,
                    ]);

                    // $deviceToken = User::where('id',$flag->user_id)->first();
                    // $title =auth()->user()->nick_name." has like your post";
                    // $body = "click to watch a video";
                    // // $user_to_sent = User::find($request->user_id);
                    // $data = [
                    //     'token'=>$deviceToken->fcm_token,
                    //     'user'=>auth()->user(),
                    //     'post'=>$flag
                    // ];
                    // $fcmtoken = $deviceToken->fcm_token;
                    // if($fcmtoken){
                    //     $this->fCMService->sendNotification($fcmtoken, $title, $body,$data);
                    // }
                    // Notification::create([
                    // 'user_id'=>auth()->user()->id,
                    // 'user'=>$deviceToken,
                    // 'channel_name'=>'post like',
                    // 'token'=>$fcmtoken,
                    // ]);

                    return response()->json([
                        'status'=>200,
                        'post'=>$post
                    ]);
        } catch (\Exception $e) {
            // Handle general exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }


    }


}


