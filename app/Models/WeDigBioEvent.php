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
use App\Models\Traits\UuidTrait;
use App\Presenters\WeDigBioDatePresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class WeDigBioEvent
 */
class WeDigBioEvent extends BaseEloquentModel
{
    use HasFactory, Presentable, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'wedigbio_events';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'start_date',
        'end_date',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'active' => 'int',
        ];
    }

    protected string $presenter = WeDigBioDatePresenter::class;

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
     * Transcriptions relation.
     */
    public function transcriptions(): HasMany
    {
        return $this->hasMany(WeDigBioEventTranscription::class, 'event_id', 'id');
    }

    /**
     * Scope for active.
     */
    public function scopeActive($query): mixed
    {
        return $query->where('active', 1);
    }
}
