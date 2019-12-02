<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsToken extends Model
{
    const EXPIRE_TIEM= 1;

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
        $min = pow(10, 4);
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
        return ! $this->isUsed() && ! $this->isExpired();
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
        return $this->created_at->diffInMinutes(Carbon::now()) > static::EXPIRATION_TIME;
    }

}
