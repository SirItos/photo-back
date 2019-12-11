<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Client extends Model
{
    public static function getClient($appName){
        return DB::table('oauth_clients')->select('id','secret')->where('name','=',$appName)->first();
    }
}
