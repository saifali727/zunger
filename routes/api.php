<?php

use App\Http\Controllers\Api\LiveStreamController;
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, PostController, UserController, CommentController, SearchController, VideoController};
use Illuminate\Support\Facades\App;
use App\Http\Controllers\SongsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('user_signup', [AuthController::class, 'signup']);

Route::post('social_login', [AuthController::class, 'social_login']);

Route::post('login', [AuthController::class, 'login']);

Route::post('user_phone_otp', [AuthController::class, 'number_verification']);

Route::post('user_phone_otp_verification', [AuthController::class, 'otp_verification']);

Route::post('password', [AuthController::class, 'create_user_password']);
Route::post('forgot_password', [AuthController::class, 'forgot_password']);
Route::post('reset_password', [AuthController::class, 'reset_password']);




Route::get('get_interests', [UserController::class, 'get_interests']);


Route::post('upload_songs', [SongsController::class, 'upload_songs']);
Route::get('get_songs', [SongsController::class, 'get_songs']);
Route::post('video_view_counts', [VideoController::class, 'video_view_counts']);
Route::middleware('auth:api')->group(function () {
    Route::post('send_fcm_notification', [FcmController::class, 'send_fcm_notification']);
    Route::get('get_notification', [FcmController::class, 'get_notification']);
    Route::post('edit_profile', [UserController::class, 'edit_profile']);

    Route::post('report', [UserController::class, 'report_user_post']);

    Route::post('block_user', [UserController::class, 'block_user']);

    Route::post('get_all_blocked_users', [UserController::class, 'get_all_blocked_users']);

    Route::post('get_user', [UserController::class, 'get_user']);

    Route::get('get_user_posts', [UserController::class, 'get_user_posts']);

    Route::post('upload_video', [PostController::class, 'upload_video']);

    Route::post('upload_story', [PostController::class, 'upload_story']);

    Route::post('delete_post', [PostController::class, 'delete_post']);

    Route::get('get_stories', [VideoController::class, 'get_stories']);

    Route::get('get_for_you_videos', [VideoController::class, 'get_for_you_videos']);

    Route::get('get_following_users', [VideoController::class, 'get_following_users']);

    Route::post('make_duet', [PostController::class, 'make_duet']);

    Route::get('all_users', [UserController::class, 'all_users']);

    Route::post('comment', [CommentController::class, 'comment']);

    Route::post('delete_comment', [CommentController::class, 'delete_comment']);

    Route::post('edit_comment', [CommentController::class, 'edit_comment']);

    Route::post('is_private_or_public', [CommentController::class, 'is_private_or_public']);

    Route::get('get_all_comments', [CommentController::class, 'get_all_comments']);

    Route::post('get_post_comments', [CommentController::class, 'get_post_comments']);

    Route::post('like_unlike_post', [PostController::class, 'like_unlike_post']);

    Route::post('favourite_unfavourite_post', [PostController::class, 'favourite_unfavourite_post']);

    Route::post('follow/unfollow_user', [UserController::class, 'follow_user']);

    // Route::post('follow/send_friend_request', [UserController::class, 'send_friend_request']);

    // Home Api
    Route::post('get_following_videos', [VideoController::class, 'get_following_videos']);


    //Users
    Route::post('save_user_interests', [UserController::class, 'save_user_interests']);

    Route::post('delete_account', [UserController::class, 'delete_account']);

    Route::post('find_friends_in_contact_list', [UserController::class, 'find_friends_in_contact_list']);

    Route::post('add_friend', [UserController::class, 'add_friend']);

    Route::post('accept_reject_friend_request', [UserController::class, 'accept_reject_friend_request']);

    Route::get('show_friend_requests', [UserController::class, 'show_friend_requests']);

    Route::get('show_friends', [UserController::class, 'show_friends']);

    //

    Route::post('search', [SearchController::class, 'search']);
    //
    Route::post('share_post', [PostController::class, 'share_post']);

    Route::get('get_friends_posts', [PostController::class, 'get_friends_posts']);

    Route::post('stream_creator', [LiveStreamController::class, 'stream_creator']);
    Route::post('stream_joiner', [LiveStreamController::class, 'stream_joiner']);
    Route::post('stream_leaver', [LiveStreamController::class, 'stream_leaver']);
    Route::post('get_stream_data', [LiveStreamController::class, 'get_stream_data']);
    Route::get('/greeting/{locale}', function ($locale) {
        if (! in_array($locale, ['en', 'ar'])) {
            abort(400);
        }

        App::setLocale($locale);

        return App::getLocale();
    });


    // live streams api's
    Route::post('start-live-stream', [LiveStreamController::class, 'start_live_stream']);
    Route::post('get_rtc_token', [LiveStreamController::class, 'get_rtc_token']);
    Route::get('get_token', [LiveStreamController::class, 'get_token']);

});

