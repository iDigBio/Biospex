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

use App\Models\WorkflowManager;

/**
 * Class WorkflowManagerRepository
 */
class WorkflowManagerRepository extends BaseRepository
{
    /**
     * WorkflowManagerRepository constructor.
     */
    public function __construct(WorkflowManager $workflowManager)
    {

        $this->model = $workflowManager;
    }

    /**
     * Get workflow managers for overnight process.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*']): \Illuminate\Database\Eloquent\Collection|array
    {
        // TODO query selects state => 1. Need to remove this and get all records and determine action by state in actor class.
        $model = $this->model->with(['expedition.stat', 'expedition.actors' => function ($query) {
            $query->where('state', '>', 0)->where('error', 0);
        }])->where('stopped', 0);

        return $expeditionId === null ?
            $model->get($attributes) :
            $model->where('expedition_id', $expeditionId)->get($attributes);
    }
}
