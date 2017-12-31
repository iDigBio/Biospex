<?php

namespace App\Models;

use App\Notifications\ApiUserResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ApiUser extends Authenticatable
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
        'password',
        'activated'
    ];

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Return activation code.
     *
     * @return string
     */
    public function getActivationCode()
    {
        $this->activation_code = $activationCode = $this->getRandomString();

        $this->save();

        return $activationCode;
    }

    /**
     * Activates user account.
     *
     * @param $activationCode
     * @return bool
     */
    public function attemptActivation($activationCode)
    {
        if ($activationCode == $this->activation_code) {
            $this->activation_code = null;
            $this->activated = true;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    public function getRandomString($length = 42)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
        }

        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
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
