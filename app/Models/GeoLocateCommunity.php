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

class GeoLocateCommunity extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'geo_locate_communities';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'name',
        'data'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = ['data' => 'array'];

    /**
     * Project relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * GeoLocateStat relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function geoLocateStats()
    {
        return $this->hasMany(GeoLocateStat::class);
    }
}
