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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MongoDB\Laravel\Eloquent\HybridRelations;

class WeDigBioEventTranscription extends BaseEloquentModel
{
    use HasFactory, HybridRelations;

    /**
     * @inheritDoc
     */
    protected $table = 'wedigbio_event_transcriptions';

    /**
     * Dates are fillable to accommodate adding missed records overnight.
     *
     * @inheritDoc
     */
    protected $fillable = [
        'classification_id',
        'project_id',
        'date_id',
        'created_at',
        'updated_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'classification_id' => 'int',
        'project_id' => 'int',
        'date_id' => 'int'
    ];

    /**
     * Transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transcription(): HasOne
    {
        return $this->hasOne(PanoptesTranscription::class,'_id', 'classification_id');
    }

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Date relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function date(): BelongsTo
    {
        return $this->belongsTo(WeDigBioEventDate::class);
    }

    /**
     * DateId scope.
     *
     * @param $query
     * @param $arg
     * @return mixed
     */
    public function scopeDateId($query, $arg): mixed
    {
        return $query->where('date_id', $arg);
    }
}
