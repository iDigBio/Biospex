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
 */
class PusherTranscription extends BaseMongoModel
{
    /**
     * Set Collection
     */
    protected $table = 'pusher_transcriptions';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'classification_id' => 'integer',
            'transcription_id' => 'string',
            'expedition_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'timestamp' => 'datetime',
        ];
    }

    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class, 'expedition_id', 'id');
    }

    public function transcription(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(PanoptesTranscription::class, 'classification_id', 'classification_id');
    }
}
