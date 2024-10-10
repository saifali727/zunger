<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    public function interest()
    {
        return $this->belongsToMany(User::class, 'user_interests', 'interest_id', 'user_id');
    }
}