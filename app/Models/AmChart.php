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

class AmChart extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'amcharts';

    /**
     * @inheritDoc
     */
    protected $fillable = ['project_id', 'series', 'data'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mutator for data column.
     *
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    /**
     * Accessor for data column.
     *
     * @param $value
     * @return false|string
     */
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for data column.
     *
     * @param $value
     */
    public function setSeriesAttribute($value)
    {
        $this->attributes['series'] = json_encode($value);
    }

    /**
     * Accessor for data column.
     *
     * @param $value
     * @return false|string
     */
    public function getSeriesAttribute($value)
    {
        return json_decode($value, true);
    }
}
