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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Workflow;

use App\Models\Expedition;
use App\Models\WorkflowManager;

class WorkflowManagerService
{
    /**
     * WorkflowManagerRepository constructor.
     */
    public function __construct(protected WorkflowManager $model) {}

    /**
     * Create.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Get first by column.
     */
    public function getFirstBy(string $column, string $value): ?WorkflowManager
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Create workflow manager process.
     */
    public function createProcess(Expedition &$expedition): string
    {
        if ($expedition->workflowManager !== null) {
            $expedition->workflowManager->stopped = 0;
            $expedition->workflowManager->save();

            return t('The expedition has been removed from the process queue.');
        } else {
            // Only start process for Zooniverse Actor.
            $sync = [
                $expedition->zooActor->id => [
                    'order' => $expedition->zooActor->pivot->order,
                    'state' => $expedition->zooActor->pivot->state === 1 ? 2 : $expedition->zooActor->pivot->state,
                ],
            ];
            $expedition->actors()->sync($sync, false);

            $this->create(['expedition_id' => $expedition->id]);

            return t('The expedition has been added to the process queue.');
        }
    }

    /**
     * Get workflow managers for overnight process.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*']): \Illuminate\Database\Eloquent\Collection|array
    {
        $model = $this->model->with(['expedition.stat', 'expedition.actors' => function ($query) {
            $query->where('state', '>', 0)->where('error', 0);
        }])->where('stopped', 0);

        return $expeditionId === null ?
            $model->get($attributes) :
            $model->where('expedition_id', $expeditionId)->get($attributes);
    }
}
