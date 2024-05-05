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
 * Class PanoptesTranscription
 *
 * @package App\Models
 */
class PanoptesTranscription extends BaseMongoModel
{

    /**
     * Set Collection
     */
    protected $collection = 'panoptes_transcriptions';

    /**
     * @var string[]
     */
    protected $casts = [
        'subject_id' => 'int',
        'classification_id' => 'int',
        'workflow_id' => 'int',
        'subject_expeditionId' => 'int',
        'subject_projectId' => 'int',
        'transcription_id' => 'string',
        'classification_started_at' => 'datetime',
        'classification_finished_at' => 'datetime'
    ];

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'subject_projectId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'subject_expeditionId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_subjectId', '_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dashboard()
    {
        return $this->hasOne(PusherTranscription::class, 'classification_id', 'classification_id');
    }
}
