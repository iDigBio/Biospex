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

use App\Models\WorkflowManager as Model;
use App\Repositories\Interfaces\WorkflowManager;

class WorkflowManagerRepository extends EloquentRepository implements WorkflowManager
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