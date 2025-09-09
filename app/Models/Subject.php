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
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Subject
 */
class Subject extends BaseMongoModel
{
    use Cacheable, HasFactory;

    /**
     * Set Collection
     */
    protected $table = 'subjects';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'project_id' => 'integer',
            'exported' => 'boolean',
        ];
    }

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['project', 'panoptesTranscriptions', 'occurrence'];
    }

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

    public function occurrence(): mixed
    {
        return $this->embedsOne(Occurrence::class, 'occurrence');
    }

    public function scopeProjectId($query, $projectId): mixed
    {
        return $query->where('project_id', (int) $projectId);
    }

    public function scopeSubjectId($query, $subjectId): mixed
    {
        return $query->where('id', $subjectId);
    }
}
