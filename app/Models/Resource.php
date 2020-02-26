<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id','address','title','long','lat','description','resource_type','cost','min_cost','max_cost', 'online','activated'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ResourceIamge::class);
    }

    public function favorite()
    {
        return $this->hasOne(Favorite::class);
    }

    public function statustitle() 
    {
        return $this->belongsTo(StatusCode::class,'status','code');
    }
}
