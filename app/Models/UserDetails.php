<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $fillable = [
        'user_id','name','email','display_phone','age_range'
    ];
}
