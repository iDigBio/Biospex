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
     * @return \App\Models\ExportQueue[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getAllExportQueueOrderByIdAsc()
    {
        return $this->model->with('expedition.project.group')
            ->where('error', 0)
            ->orderBy('id', 'asc')
            ->get('*');
    }

    /**
     * Find by id and actor.
     *
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @param array|string[] $attributes
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->model->with([
            'expedition.actors' => function($q) use($expeditionId, $actorId) {
                $q->where('expedition_id', $expeditionId);
                $q->where('actor_id', $actorId);
            },
            'expedition.project.group' => function($q){
                $q->with(['owner', 'users' => function($q){
                    $q->where('notification', 1);
                }]);
            }
        ])->whereHas('expedition.actors', function ($query) use ($expeditionId, $actorId) {
            $query->where('expedition_id', $expeditionId);
            $query->where('actor_id', $actorId);
        })->find($queueId);
    }

    /**
     * Find queue by expedition and actor ids.
     *
     * @param int $expeditionId
     * @param int $actorId
     * @return mixed
     */
    public function findByExpeditionAndActorId(int $expeditionId, int $actorId)
    {
        return $this->model->with('expedition')
            ->where('expedition_id', $expeditionId)
            ->where('actor_id', $actorId)
            ->get()->first();
    }

    /**
     * Return queue with expedition and nfnActor
     *
     * @param int $expeditionId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function findWithExpeditionNfnActor(int $expeditionId)
    {
        return $this->model->with(['expedition.nfnActor', 'expedition.stat'])->where('expedition_id', $expeditionId)->first();
    }
}