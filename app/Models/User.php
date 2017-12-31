<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasGroup;
use App\Models\Traits\UuidTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class User extends Authenticatable
{
    use HasGroup, UuidTrait, SoftCascadeTrait, SoftDeletes, Notifiable, LadaCacheTrait;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['ownGroups'];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @inheritDoc
     */
    protected $table = 'users';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'uuid',
        'email',
        'password',
        'activated'
    ];

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Attributes that should be hashed.
     *
     * @var array
     */
    protected $hashableAttributes = ['password'];


    /**
     * Import relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    /**
     * Profile relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Import notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

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
}
