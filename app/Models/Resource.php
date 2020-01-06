<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'user_id','address','title','long','lat','description','resource_type','min_cost','max_cost', 'online','activated'
    ];

    protected $appends = ['images'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorite()
    {
        return $this->hasOne(Favorite::class);
    }

    public function getImagesAttribute() 
    {
        return [];
    }



}
