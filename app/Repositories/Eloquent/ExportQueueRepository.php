<?php
/**
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

namespace App\Repositories\Eloquent;

use App\Models\ExportQueue as Model;
use App\Repositories\Interfaces\ExportQueue;

class ExportQueueRepository extends EloquentRepository implements ExportQueue
{
    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getFirstExportWithoutError(array $attributes = ['*'])
    {
        $results = $this->model->where('error', 0)->first($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        $results = $this->model->with([
            'expedition.actor',
            'expedition.project.group' => function($q){
                $q->with(['owner', 'users' => function($q){
                    $q->where('notification', 1);
                }]);
            }
        ])->whereHas('actor', function ($query) use ($expeditionId, $actorId) {
            $query->where('expedition_id', $expeditionId);
            $query->where('actor_id', $actorId);
        })->find($queueId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        $results = $this->model->with(['expedition.actor', 'expedition.project.group'])->whereHas('actor', function (
                $query
            ) use ($expeditionId, $actorId) {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            })->find($queueId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getAllExportQueueOrderByIdAsc()
    {
        $results = $this->model->where('error', 0)->orderBy('id', 'asc')->get('*');

        $this->resetModel();

        return $results;
    }
}