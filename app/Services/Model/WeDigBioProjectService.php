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

use App\Models\PanoptesProject;
use App\Models\WeDigBioProject;

/**
 * Class WeDigBioProjectService
 *
 * @package App\Services\Model
 */
class WeDigBioProjectService extends BaseModelService
{
    /**
     * PanoptesProjectService constructor.
     *
     * @param \App\Models\WeDigBioProject
     */
    public function __construct(WeDigBioProject $weDigBioProject)
    {

        $this->model = $weDigBioProject;
    }

    /**
     * Find by project and workflow ids.
     *
     * @param $projectId
     * @param $workflowId
     * @return \App\Models\PanoptesProject|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function findByProjectIdAndWorkflowId($projectId, $workflowId)
    {
        return $this->model->where('panoptes_project_id', $projectId)
            ->where('panoptes_workflow_id', $workflowId)->first();
    }
}