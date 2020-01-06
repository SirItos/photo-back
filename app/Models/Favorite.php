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
        return storage_path();
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

}
