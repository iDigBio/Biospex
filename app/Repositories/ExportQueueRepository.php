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

use App\Models\Actor;
use App\Models\ExportQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExportQueueRepository
 *
 * @package App\Repositories
 */
class ExportQueueRepository extends BaseRepository
{
    /**
     * ExportQueueRepository constructor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {

        $this->model = $exportQueue;
    }

    /**
     * Get exports for poll command.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllExportQueueOrderByIdAsc(): Collection
    {
        return $this->model->with('expedition.project.group')
            ->where('error', 0)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Create queue for export.
     *
     * @param int $expeditionId
     * @param int $actorId
     * @param int $total
     * @return \App\Models\ExportQueue
     */
    public function createQueue(int $expeditionId, int $actorId, int $total): ExportQueue
    {
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id'      => $actorId,
        ];

        $queue = $this->model->firstOrNew($attributes);
        $queue->queued = 0;
        $queue->error = 0;
        $queue->stage = 0;
        $queue->count = $total;
        $queue->processed = 0;
        $queue->save();

        return $queue;
    }

    /**
     * Get queue for retry using command.
     *
     * @return \App\Models\ExportQueue|null
     */
    public function getQueueForRetry(): ?ExportQueue
    {
        return $this->model->where('queued', 1)->first();
    }

}