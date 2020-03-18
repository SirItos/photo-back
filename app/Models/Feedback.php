<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{


    protected $fillable = ['user_id','email','description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function statustitle() 
    {
        return $this->belongsTo(StatusCode::class,'status','code');
    }

    public function answer()
    {
        return $this->hasOne(Answer::class);
    }
}
