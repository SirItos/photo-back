<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles;

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
     * Change user role
     * 
     * @param string $role
     * @return void
     */
    public function changeRole($role)
    {
        $this->syncRoles($role);
    }
    /**
     * Set the user's password (encrypted)
     * 
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
       $this->attributes['password'] = bcrypt($value);
    }     
    public function smsTokens()
    {
        return $this->hasMany(SmsToken::class);
    }

    public function userDetails() 
    {
        return $this->hasOne(UserDetails::class);
    }
    
    public function resource()
    {
        return $this->hasOne(Resource::class);
    }
    
    public function favorite()
    {
        return $this->hasMany(Favorite::class);
    }

    public function findForPassport($phone) {
        return $this->where('phone',$phone)->first();
    }
}
