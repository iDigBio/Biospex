<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use App\Models\Traits\HasGroup;
use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\UserPresenter;
use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Cacheable, HasApiTokens, HasFactory, HasGroup, Notifiable, Presentable, UuidTrait;

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'email',
        'password',
        'notification',
    ];

    protected $hidden = ['id', 'password', 'remember_token'];

    protected $hashableAttributes = ['password'];

    protected string $presenter = UserPresenter::class;

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['groups', 'ownGroups', 'imports', 'profile', 'events'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * Group relationship.
     */
    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * Group owner relationship.
     */
    public function ownGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Import relationship.
     */
    public function imports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Import::class);
    }

    /**
     * Profile relationship.
     */
    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Events relationship.
     */
    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Event::class);
    }
}
