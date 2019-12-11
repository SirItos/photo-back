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
        'password_set',
        'password',
        'phone'
    ];

    protected $hidden = [
        'password'
    ];

    protected $dates = [
        'phone_verificated'
    ];

    /**
     * Set the user's password (encrypted)
     * 
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
       $this->attributes['password'] = bcrypt($value) ;
    }     
    public function smsTokens()
    {
        return $this->hasMany(SmsToken::class);
    }

    public function findForPassport($phone) {
        return $this->where('phone',$phone)->first();
    }
}
