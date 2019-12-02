<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected  $guard_name = "api";

    protected $fillable = [
        'phone_verificated',
        'password',
        'phone'
    ];

    protected $hidden = [
        'password'
    ];

    protected $dates = [
        'phone_verificated'
    ];

    public function smsTokens()
    {
        return $this->hasMany(SmsToken::class);
    }

}
