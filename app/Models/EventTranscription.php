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

use MongoDB\Laravel\Eloquent\HybridRelations;

/**
 * Class EventTranscription
 */
class EventTranscription extends BaseEloquentModel
{
    use HybridRelations;

    /**
     * {@inheritDoc}
     */
    protected $table = 'event_transcriptions';

    /**
     * Created and Updated dates are fillable so overnight scripts can update with correct time for missing records.
     *
     * {@inheritDoc}
     */
    protected $fillable = [
        'classification_id',
        'event_id',
        'team_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'classification_id' => 'int',
            'event_id' => 'int',
            'team_id' => 'int',
            'user_id' => 'int',
        ];
    }

    /**
     * Transcription relationship.
     */
    public function transcription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PanoptesTranscription::class, '_id', 'classification_id');
    }

    /**
     * Event relationship.
     */
    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Event Team relationship.
     */
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EventTeam::class);
    }

    /**
     * Event User relationship.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EventUser::class);
    }
}
