<?php

namespace App\Models;

use App\Models\Traits\HasGroup;
use App\Models\Traits\UuidTrait;
use App\Presenters\UserPresenter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\Presentable;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class User extends Authenticatable
{
    use HasGroup, UuidTrait, Notifiable, Presentable, LadaCacheTrait;

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
        'activated',
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
     * @var string
     */
    protected $presenter = UserPresenter::class;

    /**
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();

        static::created(function ($model) {
            $model->getActivationCode();
            $profile = new Profile;
            $profile->user_id = $model->id;
            $profile->first_name = request()->input('first_name');
            $profile->last_name = request()->input('last_name');
            $model->profile()->save($profile);
        });
    }

    /**
     * Group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * Group owner relationship.
     *
     * @return mixed
     */
    public function ownGroups()
    {
        return $this->hasMany(Group::class);
    }

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
     * Events relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
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
