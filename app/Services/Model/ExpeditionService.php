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

use App\Models\Expedition;
use App\Services\Model\Traits\ModelTrait;

/**
 * Class ExpeditionService
 *
 * @package App\Services\Model
 */
class ExpeditionService
{
    use ModelTrait;

    /**
     * @var \App\Models\Expedition
     */
    private $model;

    /**
     * ExpeditionService constructor.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {

        $this->model = $expedition;
    }

    /**
     * Get expeditions for Zooniverse processing.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getExpeditionsForZooniverseProcess()
    {
        return $this->model->with([
            'panoptesProject',
            'stat',
            'nfnActor',
        ])->has('panoptesProject')->whereHas('nfnActor', function ($query) {
            $query->where('completed', 0);
        })->get();
    }

    /**
     * Get expedition download by actor.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function expeditionDownloadsByActor($projectId, $expeditionId)
    {
        return $this->model->with([
            'project.group',
            'actors.downloads' => function ($query) use ($expeditionId) {
                $query->where('expedition_id', $expeditionId);
            },
        ])->find($expeditionId);
    }

    /**
     * Get expeditions for admin index.
     *
     * @param null $userId
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return mixed
     */
    public function getExpeditionAdminIndex($userId = null, $sort = null, $order = null, $projectId = null)
    {
        $query = $this->model->with([
            'project.group',
            'stat',
            'nfnActor',
            'panoptesProject',
        ])->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * Find expedition having workflow manager by id.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function findExpeditionHavingWorkflowManager($expeditionId)
    {
        return $this->model->has('workflowManager')->find($expeditionId);
    }
}