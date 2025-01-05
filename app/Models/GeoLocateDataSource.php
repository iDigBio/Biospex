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

class GeoLocateDataSource extends BaseEloquentModel
{
    use HasFactory, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'geo_locate_data_sources';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'project_id',
        'expedition_id',
        'geo_locate_community_id',
        'data_source',
        'data',
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
            'data' => 'array',
        ];
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
     * Project relation
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Expedition relation.
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * GeoLocate community relation.
     */
    public function geoLocateCommunity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GeoLocateCommunity::class);
    }
}
