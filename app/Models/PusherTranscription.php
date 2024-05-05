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

/**
 * Class PusherTranscription
 *
 * @package App\Models
 */
class PusherTranscription extends BaseMongoModel
{
    /**
     * Set Collection
     */
    protected $collection = 'pusher_transcriptions';

    /**
     * @var string[]
     */
    protected $casts = [
        'classification_id' => 'int',
        'transcription_id' => 'string',
        'expedition_id' => 'int',
        'classification_started_at' => 'datetime',
        'classification_finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'timestamp' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'expedition_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function transcription()
    {
        return $this->belongsTo(PanoptesTranscription::class, 'classification_id', 'classification_id');
    }


}
