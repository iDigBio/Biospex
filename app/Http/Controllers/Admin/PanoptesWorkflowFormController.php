<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowIdFormRequest;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Models\Expedition;
use App\Models\PanoptesProject;
use App\Services\Permission\CheckPermission;
use Request;
use Response;
use View;

class PanoptesWorkflowFormController extends Controller
{
    /**
     * Construct
     */
    public function __construct(protected PanoptesProject $panoptesProject) {}

    /**
     * Return workflow id form.
     */
    public function edit(Expedition $expedition): \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $expedition->load('panoptesProject');

        return View::make('admin.expedition.partials.workflow-modal-body', compact('expedition'));
    }

    /**
     * Update or create the workflow id.
     */
    public function update(WorkflowIdFormRequest $request, Expedition $expedition): \Illuminate\Http\JsonResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $expedition->load('project.group');

        if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
            return Response::json(['message' => t('You are not authorized for this action.')], 401);
        }

        if (! empty($request->input('panoptes_workflow_id'))) {
            $attributes = [
                'project_id' => $expedition->project->id,
                'expedition_id' => $expedition->id,
            ];

            $values = [
                'project_id' => $expedition->project->id,
                'expedition_id' => $expedition->id,
                'panoptes_workflow_id' => $request->input('panoptes_workflow_id'),
            ];

            $panoptesProject = $this->panoptesProject->updateOrCreate($attributes, $values);

            PanoptesProjectUpdateJob::dispatch($panoptesProject);

            return Response::json(['message' => t('Workflow id is updated.')]);
        }

        return Response::json(['message' => t('Could not update Panoptes Workflow Id.')], 500);
    }
}
