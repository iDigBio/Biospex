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
 *
 * @package App\Models
 */
class EventTranscription extends BaseEloquentModel
{
    use HybridRelations;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $table = 'event_transcriptions';

    /**
     * Created and Updated dates are fillable so overnight scripts can update with correct time for missing records.
     *
     * @inheritDoc
     */
    protected $fillable = [
        'classification_id',
        'event_id',
        'team_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'classification_id' => 'int',
        'event_id' => 'int',
        'team_id' => 'int',
        'user_id' => 'int',
    ];

    /**
     * Transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transcription()
    {
        return $this->hasOne(PanoptesTranscription::class,'_id', 'classification_id');
    }

    /**
     * Event relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Event Team relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(EventTeam::class);
    }

    /**
     * Event User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(EventUser::class);
    }
}
