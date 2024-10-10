<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];

    public function report_to(){
        return $this->belongsTo(User::class,'reported_id','id');
    }
    public function report_by(){
        return $this->belongsTo(User::class,'report_by','id');
    }
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
