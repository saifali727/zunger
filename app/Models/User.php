<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $guarded = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tags_posts()
    {
        return $this->belongsToMany(Post::class, 'tags_peoples', 'user_id', 'post_id');
    }
    public function mention_posts()
    {
        return $this->belongsToMany(Post::class, 'mention_peoples', 'user_id', 'post_id');
    }

    public function like_post()
    {
        return $this->belongsToMany(Post::class, 'like_post', 'user_id', 'post_id')->withCount('like_post_user', 'comments', 'like_post_user', 'favourites', 'hash_tags')->with('comments');
    }
    public function post()
    {
        return $this->hasMany(Post::class, 'user_id', 'id')->withCount('like_post_user', 'comments', 'like_post_user', 'favourites', 'hash_tags')->with('comments', 'user');
    }

    public function favourites()
    {
        return $this->belongsToMany(Post::class, 'favourite_post', 'user_id', 'post_id')->withCount('like_post_user', 'comments', 'like_post_user', 'favourites', 'hash_tags')->with('comments');
    }
    public function follow()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follower_id')->with('post');
    }
    public function followedUsers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follower_id');
    }
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follower_id', 'user_id');
    }
    public function interest()
    {
        return $this->belongsToMany(Interest::class, 'user_interests', 'user_id', 'interest_id');
    }

    public function friend_requests_sent()
    {
        return $this->belongsToMany(User::class, 'friend_requests', 'friend_id', 'user_id',);
    }
    public function friend_requests_recieve()
    {
        return $this->belongsToMany(User::class, 'friend_requests', 'user_id', 'friend_id',);
    }

    public function friend_added()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_request', 'user_id', 'blocked_user_id');
    }

    // Define the relationship for users who have blocked this user
    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocked_request', 'blocked_user_id', 'user_id');
    }

    // public function friend_added_by()
    // {
    //     return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    // }
}
