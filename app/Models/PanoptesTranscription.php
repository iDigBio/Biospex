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

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;

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
        'subject_id' => 'integer',
        'classification_id' => 'integer',
        'workflow_id' => 'integer',
        'subject_expeditionId' => 'integer',
        'subject_projectId' => 'integer',
        'transcription_id' => 'string',
        //'classification_finished_at' => 'datetime',
        //'classification_started_at' => 'datetime' TODO check datetime casts work with mongodb
    ];

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

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

    /**
     * Mutate finished_at date for MongoDb
     *
     * @param  string  $value
     */
    public function setClassificationFinishedAtAttribute($value)
    {
        $this->attributes['classification_finished_at'] = new UTCDateTime(Carbon::parse($value)->timestamp * 1000);
    }


    /**
     * Mutate started_at for MongoDb
     *
     * @param  string  $value
     */
    public function setClassificationStartedAtAttribute($value)
    {
        $this->attributes['classification_started_at'] = new UTCDateTime(Carbon::parse($value)->timestamp * 1000);
    }

    /**
     * New method for getting and setting attributes.
     * TODO Change attribute setters and mutators.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
}
