<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SmsToken extends Model
{
    const EXPIRE_TIME = 1;

    protected $fillable = [
        'code',
        'user_id',
        'used'
    ];

    public function __construct(array $attributes = [])
    {
        if (! isset($attributes['code'])) {
            $attributes['code'] = $this->generateCode();
        }

        parent::__construct($attributes);
    }

    /**
     * Генерация 4-х значного пароля
     *
     * @return string
     */
    public function generateCode()
    {
        $min = pow(10, 3);
        $max = $min * 10 - 1;
        $code = mt_rand($min, $max);

        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Валидация смс ключа
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->isUsed()) {
            return ['valid'=>false, 'message'=>'Код уже использован'];
        }
        if ($this->isExpired()){
            return ['valid'=>false, 'message'=>'Код уже недействителен'];
        }
        return ['valid'=>true];
    }

    public function isUsed() 
    {
        return $this->used;
    }

    /**
     * Просрочен ли токен
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->created_at->diffInMinutes(Carbon::now()) >= static::EXPIRE_TIME;
    }

}
