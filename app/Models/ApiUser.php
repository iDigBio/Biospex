<?php

namespace App\Models;

use App\Notifications\ApiUserResetPasswordNotification;
use App\Notifications\VerifyApiEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ApiUser extends Authenticatable implements MustVerifyEmail
{

    use HasApiTokens, Notifiable, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'api_users';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyApiEmail());
    }

    /**
     * Send password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ApiUserResetPasswordNotification($token));
    }
}
