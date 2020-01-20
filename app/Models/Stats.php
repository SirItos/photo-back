<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    protected $fillable = ['owner_id','event'];

    public function user()
    {
       return $this->belongsTo(User::class);
    }
}
