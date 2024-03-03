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
use App\Http\Requests\GeoLocateCommunityRequest;
use App\Http\Requests\WorkflowIdFormRequest;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Repositories\ExpeditionRepository;
use App\Repositories\PanoptesProjectRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\WorkflowManagerRepository;
use Exception;
use Flash;
use Response;

class ZooniverseController extends Controller
{
    /**
     * @var \App\Repositories\ProjectRepository
     */
    private ProjectRepository $projectRepository;

    /**
     * @var \App\Repositories\WorkflowManagerRepository
     */
    private WorkflowManagerRepository $workflowManagerRepository;

    /**
     * Construct
     *
     * @param \App\Repositories\ProjectRepository $projectRepository
     * @param \App\Repositories\WorkflowManagerRepository $workflowManagerRepository
     */
    public function __construct(
        ProjectRepository $projectRepository,
        WorkflowManagerRepository $workflowManagerRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->workflowManagerRepository = $workflowManagerRepository;
    }

    /**
     * Start processing expedition actors
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(ExpeditionRepository $expeditionRepository, int $projectId, int $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        try {
            $expedition = $expeditionRepository->findWith($expeditionId, [
                'actors',
                'panoptesProject',
                'workflowManager',
                'stat',
            ]);

            if (null === $expedition->panoptesProject) {
                throw new Exception(t('Zooniverse Workflow Id is missing. Please update the Expedition once Workflow Id is acquired.'));
            }

            if (null !== $expedition->workflowManager) {
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

                $this->workflowManagerRepository->create(['expedition_id' => $expeditionId]);
                $message = t('The expedition has been added to the process queue.');
            }

            \Flash::success($message);

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (Exception $e) {
            \Flash::error(t('An error occurred when trying to process the expedition: %s', $e->getMessage()));

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Stop a expedition process.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop(int $projectId, int $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $workflow = $this->workflowManagerRepository->findBy('expedition_id', $expeditionId);

        if ($workflow === null) {
            \Flash::error(t('Expedition has no processes at this time.'));

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerRepository->update(['stopped' => 1], $workflow->id);
        \Flash::success(t('Expedition process has been stopped locally. This does not stop any processing occurring on remote sites.'));

        return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Return workflow id form.
     *
     * @param \App\Repositories\PanoptesProjectRepository $panoptesProjectRepository
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function workflowShowForm(PanoptesProjectRepository $panoptesProjectRepository, int $projectId, int $expeditionId): \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $panoptesProject = $panoptesProjectRepository->findBy('expedition_id', $expeditionId);

        return \View::make('admin.expedition.partials.workflow-modal-body', compact('projectId', 'expeditionId', 'panoptesProject'));
    }


    /**
     * Update or create the workflow id.
     *
     * @param \App\Http\Requests\WorkflowIdFormRequest $request
     * @param \App\Repositories\PanoptesProjectRepository $panoptesProjectRepository
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function workflowUpdateForm(
        WorkflowIdFormRequest $request,
        PanoptesProjectRepository $panoptesProjectRepository,
        int $projectId,
        int $expeditionId
    ): \Illuminate\Http\JsonResponse {

        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Response::json(['message' => t('You are not authorized for this action.')], 401);
        }

        if (! empty($request->input('panoptes_workflow_id'))) {
            $attributes = [
                'project_id'    => $projectId,
                'expedition_id' => $expeditionId,
            ];

            $values = [
                'project_id'           => $project->id,
                'expedition_id'        => $expeditionId,
                'panoptes_workflow_id' => $request->input('panoptes_workflow_id'),
            ];

            $panoptesProject = $panoptesProjectRepository->updateOrCreate($attributes, $values);

            PanoptesProjectUpdateJob::dispatch($panoptesProject);

            return \Response::json(['message' => t('Workflow id is updated.')]);
        }

        return \Response::json(['message' => t('Could not update Panoptes Workflow Id.')], 500);
    }
}
