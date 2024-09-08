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

namespace App\Repositories;

use App\Models\ExportQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExportQueueRepository
 */
class ExportQueueRepository extends BaseRepository
{
    /**
     * ExportQueueRepository constructor.
     */
    public function __construct(ExportQueue $exportQueue)
    {

        $this->model = $exportQueue;
    }

    /**
     * Get exports for poll command.
     */
    public function getAllExportQueueOrderByIdAsc(): Collection
    {
        return $this->model->withCount([
            'files' => function ($q) {
                $q->where('processed', 1);
            },
        ])->with('expedition.project.group')->where('error', 0)->orderBy('id', 'asc')->get();
    }

    /**
     * Create queue for export.
     */
    public function createQueue(int $expeditionId, int $actorId, int $total): ExportQueue
    {
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id' => $actorId,
        ];

        $queue = $this->model->firstOrNew($attributes);
        $queue->queued = 0;
        $queue->error = 0;
        $queue->stage = 0;
        $queue->total = $total;
        $queue->save();

        return $queue;
    }

    /**
     * Get queue for Zooniverse export.
     */
    public function findExportQueueFirst(): Model|Builder|null
    {
        return $this->model->with('expedition')->where('error', 0)->first();
    }
}
