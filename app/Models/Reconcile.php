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

use App\Models\Traits\Presentable;
use App\Presenters\ReconcilePresenter;
use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Reconcile
 */
class Reconcile extends BaseMongoModel
{
    use Cacheable, HasFactory, Presentable;

    /**
     * Set Collection
     */
    protected $table = 'reconciles';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'subject_id' => 'integer',
            'subject_projectId' => 'integer',
            'subject_expeditionId' => 'integer',
            'problem' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'timestamp' => 'datetime',
        ];
    }

    protected string $presenter = ReconcilePresenter::class;

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['project', 'expedition', 'transcriptions'];
    }

    /**
     * Subject relation.
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Expdition relation.
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class, 'expedition_id', 'id');
    }

    /**
     * Subject relation.
     */
    public function transcriptions(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_id', 'subject_id');
    }
}
