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

use App\Models\Traits\Presentable;
use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Profile
 */
class Profile extends BaseEloquentModel
{
    use Cacheable, HasFactory, Presentable;

    /**
     * {@inheritDoc}
     */
    protected $table = 'profiles';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'avatar_updated_at' => 'datetime',
            'avatar_created_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'timezone',
        'avatar',
        'avatar_path',
    ];

    protected string $presenter = \App\Presenters\ProfilePresenter::class;

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['user'];
    }

    /**
     * Profile constructor.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
