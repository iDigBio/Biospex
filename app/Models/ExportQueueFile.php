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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\HybridRelations;

/**
 * Class ExportQueueFile
 */
class ExportQueueFile extends BaseEloquentModel
{
    use HasFactory, HybridRelations;

    /**
     * @ineritDoc
     */
    protected $table = 'export_queue_files';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'queue_id',
        'subject_id',
        'access_uri',
        'message',
        'processed',
        'tries',
    ];

    /**
     * ExportQueue relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function queue()
    {
        return $this->belongsTo(ExportQueue::class, 'queue_id', 'id');
    }

    /**
     * Subject relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subject()
    {
        return $this->hasOne(Subject::class, '_id', 'subject_id');
    }

    /**
     * Define the message attribute.
     */
    protected function message(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value === '' ? null : $value,
        );
    }
}
