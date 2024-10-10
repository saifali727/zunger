<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public function tags()
    {
        return $this->belongsToMany(User::class, 'tags_peoples', 'post_id', 'user_id');
    }
    public function mention_posts()
    {
        return $this->belongsToMany(User::class, 'mention_peoples', 'post_id', 'user_id');
    }
    public function hash_tags()
    {
        return $this->belongsToMany(User::class, 'hashtags_videos', 'post_id', 'hash_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'post_id', 'id')->with('user');
    }

    public function like_post_user()
    {
        return $this->belongsToMany(User::class, 'like_post', 'post_id', 'user_id');
    }

    public function favourites()
    {
        return $this->belongsToMany(User::class, 'favourite_post', 'post_id', 'user_id',);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
