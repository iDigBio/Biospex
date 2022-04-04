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

use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;

/**
 * Class PanoptesTranscription
 *
 * @package App\Models
 */
class PanoptesTranscriptionNew extends BaseMongoModel
{

    /**
     * Set Collection
     */
    protected $collection = 'panoptes_transcriptions_new';

    /**
     * @var string[]
     */
    protected $casts = [
        'subject_id' => 'int',
        'classification_id' => 'int',
        'workflow_id' => 'int',
        'subject_expeditionId' => 'int',
        'subject_projectId' => 'int',
        'transcription_id' => 'string'
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
}
