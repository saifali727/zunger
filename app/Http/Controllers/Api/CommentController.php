<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comments;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\{Notification};
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;
use function PHPSTORM_META\map;
use App\Services\FCMService;

class CommentController extends Controller
{
    private $fCMService;
    public function __construct(FCMService $fCMService)
    {
        $this->fCMService = $fCMService;
    }
    public function comment(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'post_id' => 'required',
        ]);
        $outputPath = "";
        $url_update = " ";
        $request['user_id'] = auth()->user()->id;
        if($request->hasFile('media')){
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();
            $filename = rand(1111, 9999) . "" . time() . "." . $extension;
            $url =  Storage::disk('local')->put("public/comments", $file);
            $urls3 =  Storage::disk('s3')->put("public/comments", $file);
            // $path = public_path() . '/uploads/';
            // $url =  $file->move($path, $filename);
            // $url_update = str_replace('public', 'storage', $url);
            $request['url']= 'https://d1s3gnygbw6wyo.cloudfront.net/'.$urls3;
            $outputPath = 'comment_thumbnails/'.uniqid().'.jpg';
            FFMpeg::fromDisk('local')
            ->open($url)
            ->getFrameFromSeconds(0)
            ->export()
            ->toDisk('s3')
            ->save($outputPath);
            $request['thumbnail'] = 'https://d1s3gnygbw6wyo.cloudfront.net/'.$outputPath;
        }
        if(isset($request->comment_id)){
            $request['parent_id'] = $request->comment_id;
            $parent_comment = Comments::find($request->comment_id);
        }
        $comments =  Comments::create($request->except('media','locale','comment_id'));
        if(isset($request->comment_id)){
            $parent_comment->child_id = $comments->id;
        }

        $post_user = Post::where('id',$request->post_id)->with('user')->first();
        // return $post_user;
        $title ="Comment Notification";
        $body = auth()->user()->nick_name." comments on your video";
        // $user_to_sent = User::find($request->user_id);
        $data = [
            'token'=>$post_user->user->fcm_token,
        ];
        $fcmtoken = $post_user->user->fcm_token;

        Notification::create([
            'user_id'=>$post_user->user->id,
            'user'=>auth()->user(),
            'channel_name'=>"user comment",
            'token'=>$fcmtoken,
        ]);
        try{
            if($fcmtoken){
                $this->fCMService->sendNotification($fcmtoken, $title, $body,$data);
            }
        }
        catch(\Exception $e){
            // return response()->json([
            //     "status"=>500,
            //   "message"=>$e->getMessage()
            // ],500);
            return response()->json([
                'status' => 200,
                'message' => __('auth.comment added on post successfully'),
                'comment' => $comments,
                'thumbnail'=>$outputPath ? 'https://d1s3gnygbw6wyo.cloudfront.net/'.$outputPath : '' ,
            ]);
        }


        return response()->json([
            'status' => 200,
            'message' => __('auth.comment added on post successfully'),
            'comment' => $comments,
            'thumbnail'=>$outputPath ? 'https://d1s3gnygbw6wyo.cloudfront.net/'.$outputPath : '' ,
        ]);
    }

    public function get_all_comments(Request $request)
    {
        App::setLocale($request->locale);
        $comments = Comments::all();
        if ($comments) {
            return response()->json([
                "status" => 200,
                "data" => $comments,
            ]);
        } else {
            return response()->json([
                "status" => 200,
                "message" => __('auth.no comment found'),
            ]);
        }
    }

    public function get_post_comments(Request $request)
    {
        App::setLocale($request->locale);
        $validated = $request->validate([
            'post_id' => 'required',
        ]);

        // Get the post and check if it exists
        $post = Post::find($request->post_id);
        if (!$post) {
            return response()->json([
                "status" => 404,
                "message" => __('auth.post not found'),
            ]);
        }

        // Check if the user is the owner of the post
        $isOwner = $post->user_id === $request->user()->id;

        // Get comments based on visibility rules
        if ($isOwner) {
            $comments = Comments::where('post_id', $request->post_id)->whereNull('parent_id')->with('comments')->get();
        } else {
            $comments = Comments::where('post_id', $request->post_id)
                            ->where('is_private', false)->whereNull('parent_id')->with('comments') // Only public comments
                            ->get();
        }

        return response()->json([
            "status" => 200,
            "data" => $comments,
        ]);
    }

    public function delete_comment(Request $request) {
        $comment = Comments::find($request->comment_id);

        if (!$comment) {
            return response()->json([
                "status" => 404,
                "message" => __('auth.comment not found'),
            ]);
        }

        // Delete child comments recursively
        $this->deleteChildComments($request->comment_id);

        // Delete the parent comment
        if ($comment->url) {
            unlink($comment->url);
        }
        $comment->delete();

        return response()->json([
            "status" => 200,
            "message" => __('auth.comment deleted successfully'),
        ]);
    }

    private function deleteChildComments($parent_id) {
        $childComments = Comments::where('parent_id', $parent_id)->get();

        foreach ($childComments as $childComment) {
            if ($childComment->url && file_exists($childComment->url)) {
                unlink($childComment->url);
            }
            $childComment->delete();

            // Recursively delete child comments of the current child
            $this->deleteChildComments($childComment->id);
        }
    }

    public function edit_comment(Request $request){
        App::setLocale($request->locale);
        $validated = $request->validate([
            'comment_id' => 'required',
        ]);
        $comment = Comments::find($request->comment_id);

        $url_update = " ";
        $request['user_id'] = auth()->user()->id;
        if($request->hasFile('media')){
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();
            $filename = rand(1111, 9999) . "" . time() . "." . $extension;
            $url =  Storage::disk('s3')->put("public/comments", $file);
            $url_update = 'https://d1s3gnygbw6wyo.cloudfront.net/'.$url; // Uncomment this line
            $request['url']= $url_update;
        }
        // if($comment->url && file_exists($comment->url)){
        //     unlink($comment->url);
        // }
        $comment->update([
            'url'=>$request->url,
            'comment'=>$request->comment,
            'is_private'=>$request->is_private,
        ]);
        $comment = Comments::find($comment->id);
        return response()->json([
            'status' => 200,
            'message' => __('auth.comment updated successfully'),
            'comment' => $comment,
            'thumbnail'=>$url_update ? $url_update : '' ,
        ]);
    }

    public function is_private_or_public(Request $request){
        $comment = Comments::find($request->comment_id);
        if($comment->user_id == auth()->user()->id){
                if($comment->is_private){
                    $comment->update(['is_private' =>0]);
                    return response()->json([
                        'status'=>201,
                        'message'=>"comment update to public successfully",
                    ]);
                }
                else{
                    $comment->update(['is_private' =>1]);
                    return response()->json([
                        'status'=>201,
                        'message'=>"comment update to private successfully",
                    ]);
                }

        }
        else{
            return response()->json([
                'status'=>401,
                'message'=>'forbidden'
            ],401);
        }



    }
}
