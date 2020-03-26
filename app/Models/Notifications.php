<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notifications extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','description','title'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
