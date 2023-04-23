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

use App\Models\Traits\Presentable;
use App\Presenters\WeDigBioDatePresenter;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeDigBioEventDate extends BaseEloquentModel
{
    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'wedigbio_event_dates';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'start_date',
        'end_date',
        'active'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'int'
    ];

    /**
     * @var string
     */
    protected string $presenter = WeDigBioDatePresenter::class;

    /**
     * Transcriptions relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptions(): HasMany
    {
        return $this->hasMany(WeDigBioEventTranscription::class, 'date_id', 'id');
    }

    /**
     * Scope for active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('active', 1);
    }
}
