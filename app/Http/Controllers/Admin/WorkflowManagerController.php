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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowIdFormRequest;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Models\PanoptesProject;
use App\Services\Expedition\ExpeditionService;
use App\Services\Models\ProjectModelService;
use App\Services\Models\WorkflowManagerModelService;
use Exception;

class WorkflowManagerController extends Controller
{
    /**
     * Construct
     */
    public function __construct(
        private ProjectModelService $projectModelService,
        private WorkflowManagerModelService $workflowManagerModelService
    ) {}

    /**
     * Start processing expedition actors
     */
    public function process(ExpeditionService $expeditionService, int $projectId, int $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        try {
            $expedition = $expeditionService->expedition->with([
                'actors',
                'panoptesProject',
                'workflowManager',
                'stat',
            ])->find($expeditionId);

            if ($expedition->panoptesProject === null) {
                throw new Exception(t('Zooniverse Workflow Id is missing. Please update the Expedition once Workflow Id is acquired.'));
            }

            if ($expedition->workflowManager !== null) {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
                $message = t('The expedition has been removed from the process queue.');
            } else {
                // Dont't start GeoLocateExport Actor.
                $expedition->actors->reject(function ($actor) {
                    return $actor->id == config('geolocate.actor_id');
                })->each(function ($actor) use ($expedition) {
                    $sync = [
                        $actor->id => [
                            'order' => $actor->pivot->order,
                            'state' => $actor->pivot->state === 1 ? 2 : $actor->pivot->state,
                        ],
                    ];
                    $expedition->actors()->sync($sync, false);
                });

                $this->workflowManagerModelService->create(['expedition_id' => $expeditionId]);
                $message = t('The expedition has been added to the process queue.');
            }

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])->with('success', $message);
        } catch (Exception $e) {

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])
                ->with('danger', t('An error occurred when trying to process the expedition: %s', $e->getMessage()));
        }
    }

    /**
     * Stop a expedition process.
     */
    public function stop(int $projectId, int $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $workflow = $this->workflowManagerModelService->getFirstBy('expedition_id', $expeditionId);

        if ($workflow === null) {

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])
                ->with('danger', t('Expedition has no processes at this time.'));
        }

        $workflow->stopped = 1;
        $this->workflowManagerModelService->update(['stopped' => 1], $workflow->id);

        return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])
            ->with('success', t('Expedition process has been stopped locally. This does not stop any processing occurring on remote sites.'));
    }

    /**
     * Return workflow id form.
     */
    public function workflowShowForm(PanoptesProject $panoptesProjectModel, int $projectId, int $expeditionId): \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $panoptesProject = $panoptesProjectModel->where('expedition_id', $expeditionId)->first();

        return \View::make('admin.expedition.partials.workflow-modal-body', compact('projectId', 'expeditionId', 'panoptesProject'));
    }

    /**
     * Update or create the workflow id.
     */
    public function workflowUpdateForm(
        WorkflowIdFormRequest $request,
        PanoptesProject $panoptesProjectModel,
        int $projectId,
        int $expeditionId
    ): \Illuminate\Http\JsonResponse {

        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Response::json(['message' => t('You are not authorized for this action.')], 401);
        }

        if (! empty($request->input('panoptes_workflow_id'))) {
            $attributes = [
                'project_id' => $projectId,
                'expedition_id' => $expeditionId,
            ];

            $values = [
                'project_id' => $project->id,
                'expedition_id' => $expeditionId,
                'panoptes_workflow_id' => $request->input('panoptes_workflow_id'),
            ];

            $panoptesProject = $panoptesProjectModel->updateOrCreate($attributes, $values);

            PanoptesProjectUpdateJob::dispatch($panoptesProject);

            return \Response::json(['message' => t('Workflow id is updated.')]);
        }

        return \Response::json(['message' => t('Could not update Panoptes Workflow Id.')], 500);
    }
}
