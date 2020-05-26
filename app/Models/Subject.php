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

class Subject extends BaseMongoModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_subjectId');
    }

    /**
     * @return \Jenssegers\Mongodb\Relations\EmbedsOne
     */
    public function occurrence()
    {
        return $this->embedsOne(Occurrence::class, 'occurrence');
    }

    /**
     * Set project id attribute.
     *
     * @param $value
     * @return int
     */
    public function setProjectId($value)
    {
        return $this->attributes['project_id'] = (int) $value;
    }

    /**
     * @param $query
     * @param $projectId
     * @return mixed
     */
    public function scopeProjectId($query, $projectId)
    {
        return $query->where('project_id', (int) $projectId);
    }

    /**
     * @param $query
     * @param $subjectId
     * @return mixed
     */
    public function scopeSubjectId($query, $subjectId)
    {
        return $query->where('_id', $subjectId);
    }

}
