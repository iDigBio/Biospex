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

namespace App\Services\Model;

use App\Models\WorkflowManager;
use App\Services\Model\Traits\ModelTrait;

/**
 * Class WorkflowManagerService
 *
 * @package App\Services\Model
 */
class WorkflowManagerService
{
    use ModelTrait;

    /**
     * @var \App\Models\WorkflowManager
     */
    private $model;

    /**
     * WorkflowManagerService constructor.
     *
     * @param \App\Models\WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {

        $this->model = $workflowManager;
    }

    /**
     * Get workflow managers for overnight process.
     *
     * @param null $expeditionId
     * @param array|string[] $attributes
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*'])
    {
        $model =$this->model->with(['expedition.stat', 'expedition.actors' => function($query){
            $query->where('error', 0);
            $query->where('queued', 0);
            $query->where('completed', 0);
        }])->where('stopped', '=', 0);

        return $expeditionId === null ?
            $model->get($attributes) :
            $model->where('expedition_id', $expeditionId)->get($attributes);
    }
}