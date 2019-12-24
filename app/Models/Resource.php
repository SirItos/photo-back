<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
       protected $fillable = [
        'user_id','address','long','lat','description','resource_type','min_cost','max_cost'
    ];
}
