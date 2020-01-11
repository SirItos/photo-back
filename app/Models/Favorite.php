<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'user_id','resource_id'
    ];  

    protected $appends = ['img'];

    public function getImgAttribute() 
    {
        $resource = $this->resource()->with('images:id,resource_id,url')->first();
        if (count($resource->images)) {
            return $resource->images[0];
        } else {
            return null;
        }
        
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
        
    }

}
