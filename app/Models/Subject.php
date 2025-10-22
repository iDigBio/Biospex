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

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Subject
 */
class Subject extends BaseMongoModel
{
    use HasFactory;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all expeditions that this subject belongs to.
     */
    public function getExpeditionsAttribute()
    {
        if (! isset($this->attributes['expedition_ids']) || ! is_array($this->attributes['expedition_ids'])) {
            return collect();
        }

        return \App\Models\Expedition::whereIn('id', $this->attributes['expedition_ids'])->get();
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
