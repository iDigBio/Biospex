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

class GeoLocateDataSource extends BaseEloquentModel
{
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
     * Geo Locate community relation.
     */
    public function geoLocateCommunity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GeoLocateCommunity::class);
    }
}
