<?php

namespace App\Models;




//use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;
//use Cartalyst\Sentry\Hashing\NativeHasher;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasManyImportsTrait;
use App\Models\Traits\HasOneProfileTrait;
use App\Models\Traits\HasManyUserGridFieldTrait;

class UserOrig extends SentryUser
{
    use SoftDeletes;
    use HasManyImportsTrait;
    use HasOneProfileTrait;
    use HasManyUserGridFieldTrait;

    /**
     * Protect date columns.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Allow soft deletes
     */
    protected $softDelete = true;

    /**
     * Used during phpunit tests for setting hash
     */
    public static function boot()
    {
        parent::boot();

        //  Used during phpunit tests for setting hash
        self::$hasher = new NativeHasher;

        static::created(function ($model) {
            $profile = new Profile;
            $profile->user_id = $model->id;
            $profile->save();
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $data
     * @return array|mixed
     */
    public function create($data = [])
    {
        $register = isset($data['registeruser']) ? false : true;
        $user = $this->sentry->register([
            'email'    => e($data['email']),
            'password' => e($data['password']),
        ], $register);
        $user->profile->first_name = e($data['first_name']);
        $user->profile->last_name = e($data['last_name']);
        $user->profile->save();

        return $user;
    }
}
