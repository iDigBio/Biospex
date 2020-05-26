<?php
/**
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

use MongoDB\BSON\ObjectID;

class PusherTranscription extends BaseMongoModel
{
    /**
     * Set Collection
     */
    protected $collection = 'pusher_transcriptions';

    /**
     * @inheritDoc
     */
    protected $dates = ['created_at', 'updated_at', 'timestamp'];

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

    /**
     * Set expedition_id attribute to integer.
     *
     * @param $value
     */
    public function setExpeditionIdAttribute($value)
    {
        $this->attributes['expedition_id'] = (int) $value;
    }

    /**
     * Set transcript as mongo id.
     *
     * @param $value
     */
    public function setTranscriptionIdAttribute($value)
    {
        if (is_string($value) and strlen($value) === 24 and ctype_xdigit($value)) {
            $this->attributes['transcription_id'] = new ObjectID($value);
        }
    }

    /**
     * Get transcript id.
     *
     * @param $value
     * @return mixed
     */
    public function getTranscriptIdAttribute($value)
    {
        return $this->getIdAttribute($value);
    }
}
