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

/**
 * Class OcrQueue
 */
class OcrQueue extends BaseEloquentModel
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'ocr_queues';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'project_id',
        'expedition_id',
        'total',
        'status',
        'error',
    ];

    /**
     * Project relation
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Expedition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * OcrQueueFiles relation.
     */
    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OcrQueueFile::class, 'queue_id', 'id');
    }
}
