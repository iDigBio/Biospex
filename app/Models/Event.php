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
use App\Presenters\EventPresenter;
use DateTimeZone;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Event
 *
 * @package App\Models
 */
class Event extends BaseEloquentModel
{
    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'events';

    /**
     *
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'project_id'    => 'integer',
            'owner_id'      => 'integer',
            'title'         => 'string',
            'description'   => 'string',
            'hashtag'       => 'string',
            'contact'       => 'string',
            'contact_email' => 'string',
            'start_date'    => 'datetime',
            'end_date'      => 'datetime',
            'timezone'      => 'string',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'owner_id',
        'title',
        'description',
        'hashtag',
        'contact',
        'contact_email',
        'start_date',
        'end_date',
        'timezone'
    ];

    /**
     * @var string $presenter
     */
    protected string $presenter = EventPresenter::class;

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Owner relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * EventTeam relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventTeam::class);
    }

    /**
     * Event transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventTranscription::class);
    }

    /**
     * Define the start date attribute.
     *
     * @return Attribute
     */
    protected function startDate(): Attribute
    {
        return Attribute::make(set: fn($value) => $value->setTimezone(new DateTimeZone('UTC')));
    }

    /**
     * Define the end date attribute.
     *
     * @return Attribute
     */
    protected function endDate(): Attribute
    {
        return Attribute::make(set: fn($value) => $value->setTimezone(new DateTimeZone('UTC')));
    }
}
