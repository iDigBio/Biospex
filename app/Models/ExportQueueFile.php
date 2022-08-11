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

use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * Class ExportQueueFile
 *
 * @method inComplete()
 * @method completed()
 * @package App\Models
 */
class ExportQueueFile extends BaseEloquentModel
{
    use HybridRelations;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @ineritDoc
     */
    protected $table = 'export_queue_files';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'queue_id',
        'subject_id',
        'url',
        'completed',
        'error_message'
    ];

    /**
     * ExportQueue relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function queue()
    {
        return $this->belongsTo(ExportQueue::class);
    }

    /**
     * Subject relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subject()
    {
        return $this->hasOne(Subject::class, '_id', 'subject_id');
    }

    /**
     * Scope for completed.
     *
     * @param $query
     * @return void
     */
    public function scopeCompleted($query)
    {
        $query->where('completed', 1);
    }

    /**
     * Scope for incomplete.
     *
     * @param $query
     * @return void
     */
    public function scopeInComplete($query)
    {
        $query->where('completed', 0);
    }
}