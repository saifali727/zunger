<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\FCMService;
class VideoController extends Controller
{
    public function __construct(FCMService $fCMService)
    {
        $this->fCMService = $fCMService;
    }
    public function get_following_videos(Request $request)
    {

        App::setLocale($request->locale);
        $authenticatedUser = auth()->user();
        $followedUserIds = $authenticatedUser->followedUsers()->pluck('users.id');

        $posts = Post::whereIn('user_id', $followedUserIds)->with('comments', 'user')->withCount('comments', 'like_post_user', 'favourites', 'hash_tags')->get();

        $posts = $posts->map(function ($post) use ($authenticatedUser) {
        $post->is_liked = $authenticatedUser->likedPosts && $authenticatedUser->likedPosts->contains($post) ? 1 : 0;
        return $post;
        });

        // Return the posts or pass them to the view
        return response()->json($posts);
    }
    public function get_for_you_videos(Request $request)
    {
        App::setLocale($request->locale);

        $blockedUserIds = auth()->user()->blockedUsers()->pluck('id');

        $videos = Post::whereNotIn('user_id', $blockedUserIds)
            ->with('like_post_user')
            ->withCount('comments', 'like_post_user', 'favourites', 'hash_tags')
            ->with('comments', 'user')
            ->inRandomOrder()
            ->get()
            ->map(function ($video) {
                $video->is_liked = $video->like_post_user && $video->like_post_user->contains(auth()->id()) ? 1 : 0;
                return $video;
            });

        // Return the videos or pass them to the view
        return response()->json($videos);
    }

    public function get_stories()
    {
        $posts = Post::where('type', 'story')->withCount('comments', 'like_post_user', 'favourites', 'hash_tags')->get();
        return Response()->json([
            'status' => '200',
            'stories' => $posts,
        ]);
    }
    public function get_following_users()
    {
        $user = User::find(auth()->user()->id);
        $users = $user->follow()->get();
        return $users;
    }
    public function video_view_counts(Request $request){
        $request->validate([
            'post_id'=>'required|exists:posts,id'
        ]);
        try{
            $post = Post::where('id',$request->post_id)->first();
            $post->update([
                'views'=>$post->views+1,
            ]);
            return response()->json([
                'status'=>200,
                'message'=>'post viewed successfully'
            ]);
        }
        catch(\Exception $e){
            return $e->getMessage();
        }

    }

}
