<?php

namespace App\Models;

use App\Models\Traits\HasGroup;
use App\Models\Traits\UuidTrait;
use App\Presenters\UserPresenter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\Presentable;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class User extends Authenticatable implements MustVerifyEmail
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
        'password'
    ];

    /**
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime'];

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
}
