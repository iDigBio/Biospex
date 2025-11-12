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

use App\Traits\Presentable;
use App\Traits\UuidTrait;
use App\Presenters\EventTeamPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class EventTeam
 */
class EventTeam extends BaseEloquentModel
{
    use HasFactory, Presentable, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'event_teams';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'users',
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

    protected string $presenter = EventTeamPresenter::class;

    /**
     * Model Boot
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();

    }

    /**
     * Event relationship.
     */
    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * EventUser relationship.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EventUser::class, 'event_team_user', 'team_id', 'user_id');
    }

    /**
     * Event transcription relationship.
     */
    public function transcriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventTranscription::class, 'team_id');
    }
}
