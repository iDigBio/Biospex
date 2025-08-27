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
 * Class TranscriptionLocation
 */
class TranscriptionLocation extends BaseEloquentModel
{
    use Cacheable, HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'transcription_locations';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'classification_id',
        'project_id',
        'expedition_id',
        'state_county_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'classification_id' => 'int',
            'project_id' => 'int',
            'expedition_id' => 'int',
            'state_county_id' => 'int',
        ];
    }

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['project', 'expedition', 'panoptesTranscription', 'stateCounty'];
    }

    /**
     * Return Project relationship.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Return Expedition relation.
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Return PanoptesTranscription relation.
     */
    public function panoptesTranscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PanoptesTranscription::class, 'classification_id', 'classification_id');
    }

    /**
     * Return StateCounty relation.
     */
    public function stateCounty()
    {
        return $this->belongsTo(StateCounty::class);
    }
}
