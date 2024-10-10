<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SteamInfo extends Model
{
    use HasFactory;
    protected $table = 'steam_infos';
    protected $guarded = [];
}
