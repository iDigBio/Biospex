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
        protected Javascript $javascript,
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
     * Create expedition.
     */
    public function expeditionCreate(Project $project): void
    {
        $this->setModel($project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => [],
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.create', [$project]),
            'exportUrl' => route('admin.grids.export', [$project]),
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
            'dataUrl' => route('admin.grids.show', [$expedition]),
            'exportUrl' => route('admin.grids.expedition.export', [$expedition]),
            'checkbox' => false,
            'route' => 'show', // used for export
        ]);
    }

    public function expeditionEdit(Expedition $expedition, array $subjectIds): void
    {
        $this->setModel($expedition->project);

        $this->javascript->put([
            'model' => $this->model,
            'subjectIds' => $subjectIds,
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.edit', [$expedition]),
            'exportUrl' => route('admin.grids.expedition.export', [$expedition]),
            'checkbox' => $expedition->workflowManager === null,
            'route' => 'edit', // used for export
        ]);
    }
}
