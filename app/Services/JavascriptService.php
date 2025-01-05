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

namespace App\Services;

use App\Models\Expedition;
use App\Models\Project;
use App\Services\Grid\JqGridEncoder;
use App\Services\Workflow\WorkflowService;
use Laracasts\Utilities\JavaScript\Transformers\Transformer as Javascript;

class JavascriptService
{
    private string $model;

    /**
     * JavascriptService constructor.
     */
    public function __construct(
        public Javascript $javascript,
        protected JqGridEncoder $jqGridEncoder,
        protected WorkflowService $workflowService
    ) {}

    /**
     * Set model.
     */
    private function setModel(Project $project): void
    {
        $this->model = $this->jqGridEncoder->loadGridModel($project->id);
    }

    /**
     * Explore project.
     */
    public function projectExplore(Project $project): void
    {
        $this->setModel($project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => [],
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.project.index', [$project]),
            'exportUrl' => route('admin.grids.projects.export', [$project]),
            'checkbox' => false,
            'route' => 'explore', // used for export
        ]);
    }

    /**
     * Create expedition.
     */
    public function expeditionCreate(Project $project): void
    {
        $this->setModel($project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => [],
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.expeditions.create', [$project]),
            'exportUrl' => route('admin.grids.projects.export', [$project]),
            'checkbox' => true,
            'route' => 'create', // used for export and create
        ]);
    }

    /**
     * Show expedition.
     */
    public function expeditionShow(Expedition $expedition): void
    {
        $this->setModel($expedition->project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => [],
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.expeditions.show', [$expedition]),
            'exportUrl' => route('admin.grids.expeditions.export', [$expedition]),
            'checkbox' => false,
            'route' => 'show', // used for export
        ]);
    }

    /**
     * Edit expedition.
     */
    public function expeditionEdit(Expedition $expedition, array $subjectIds): void
    {
        $this->setModel($expedition->project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => $subjectIds,
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.expeditions.edit', [$expedition]),
            'exportUrl' => route('admin.grids.expeditions.export', [$expedition]),
            'checkbox' => $expedition->workflowManager === null,
            'route' => 'edit', // used for export
        ]);
    }

    /**
     * Show project.
     */
    public function projectShow(Project $project): void
    {
        $this->javascript->put([
            'max' => $max,
            'states' => $states->toJson(),
            'years' => $years,
            'project' => $project->id,
        ]);
    }
}
