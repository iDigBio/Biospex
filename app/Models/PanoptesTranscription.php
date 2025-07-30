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

use IDigAcademy\AutoCache\Traits\Cacheable;

/**
 * Class PanoptesTranscription
 */
class PanoptesTranscription extends BaseMongoModel
{
    use Cacheable;

    /**
     * Set Collection
     */
    protected $table = 'panoptes_transcriptions';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'subject_id' => 'integer',
            'classification_id' => 'integer',
            'workflow_id' => 'integer',
            'subject_expeditionId' => 'integer',
            'subject_projectId' => 'integer',
            'transcription_id' => 'string',
            'classification_started_at' => 'datetime',
            'classification_finished_at' => 'datetime',
        ];
    }

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class, 'subject_projectId', 'id');
    }

    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class, 'subject_expeditionId', 'id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_subjectId', '_id');
    }

    public function dashboard(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PusherTranscription::class, 'classification_id', 'classification_id');
    }
}
