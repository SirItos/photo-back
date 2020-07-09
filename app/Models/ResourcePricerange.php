<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourcePricerange extends Model
{
    protected $fillable = ['resource_id','min_cost','max_cost'];

    
}
