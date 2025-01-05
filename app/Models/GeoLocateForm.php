<?php

/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use App\Models\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoLocateForm extends BaseEloquentModel
{
    use HasFactory, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'geo_locate_forms';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'group_id',
        'name',
        'source',
        'hash',
        'fields',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'created_at' => 'datetime:Y-m-d',
        ];
    }

    /**
     * Group relation.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function expeditions(): HasMany
    {
        return $this->hasMany(Expedition::class);
    }
}
