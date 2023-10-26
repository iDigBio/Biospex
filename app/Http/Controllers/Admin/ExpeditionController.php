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
use App\Http\Requests\ExpeditionFormRequest;
use App\Jobs\DeleteExpedition;
use App\Repositories\ProjectRepository;
use App\Services\Grid\JqGridEncoder;
use App\Services\Process\ExpeditionService;
use Auth;
use Exception;
use Flash;
use JavaScript;

/**
 * Class ExpeditionController
 *
 * @package App\Http\Controllers\Admin
 */
class ExpeditionController extends Controller
{
    /**
     * @var \App\Repositories\ProjectRepository
     */
    private ProjectRepository $projectRepository;

    /**
     * @var \App\Services\Process\ExpeditionService
     */
    private ExpeditionService $expeditionService;

    /**
     * ExpeditionController constructor.
     *
     * @param \App\Repositories\ProjectRepository $projectRepository
     * @param \App\Services\Process\ExpeditionService $expeditionService
     */
    public function __construct(ProjectRepository $projectRepository, ExpeditionService $expeditionService)
    {
        $this->projectRepository = $projectRepository;
        $this->expeditionService = $expeditionService;
    }

    /**
     * Display all expeditions for user.
     *
     * @return \Illuminate\View\View
     */
    public function index(): \Illuminate\View\View
    {
        $results = $this->expeditionService->getAdminIndex(\Auth::user()->id);

        [$expeditions, $expeditionsCompleted] = $results->partition(function ($expedition) {
            return $expedition->completed === 0;
        });

        return \View::make('admin.expedition.index', compact('expeditions', 'expeditionsCompleted'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $projectId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create($projectId, JqGridEncoder $grid)
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $workflowOptions = $this->expeditionService->getWorkflowSelect();

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => [],
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.create', [$project->id]),
            'exportUrl'  => route('admin.grids.export', [$projectId]),
            'checkbox'   => true,
            'route'      => 'create', // used for export
        ]);

        return \View::make('admin.expedition.create', compact('project', 'workflowOptions'));
    }

    /**
     * Store new expedition.
     *
     * @param \App\Http\Requests\ExpeditionFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request, $projectId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $expedition = $this->expeditionService->createExpedition($request->all());
        if (! $expedition) {
            \Flash::error(t('An error occurred when saving record.'));

            return \Redirect::route('admin.projects.show', [$project->id]);
        }
        $expedition->load('workflow.actors.contacts');

        $this->expeditionService->setSubjectIds($request->get('subject-ids'));;
        $this->expeditionService->attachSubjects($expedition->id);
        $this->expeditionService->syncActors($expedition);
        $this->expeditionService->syncStat($expedition);

        $this->expeditionService->notifyActorContacts($expedition, $project);

        \Flash::success(t('Record was created successfully.'));

        return \Redirect::route('admin.expeditions.show', [$projectId, $expedition->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($projectId, $expeditionId, JqGridEncoder $grid): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $expedition = $this->expeditionService->findExpeditionWithRelations($expeditionId);

        if (! $this->checkPermissions('readProject', $expedition->project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => [],
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.show', [$expedition->project->id, $expedition->id]),
            'exportUrl'  => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'checkbox'   => false,
            'route'      => 'show', // used for export
        ]);

        return \View::make('admin.expedition.show', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function clone($projectId, $expeditionId, JqGridEncoder $grid): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $expedition = $this->expeditionService->findExpeditionWithRelations($expeditionId);

        if (! $this->checkPermissions('create', $expedition->project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $workflowOptions = $this->expeditionService->getWorkflowSelect();

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => [],
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.create', [$expedition->project->id]),
            'exportUrl'  => route('admin.grids.export', [$projectId]),
            'checkbox'   => true,
            'route'      => 'create', // used for export
        ]);

        return \View::make('admin.expedition.clone', compact('expedition', 'workflowOptions'));
    }

    /**
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($projectId, $expeditionId, JqGridEncoder $grid): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $expedition = $this->expeditionService->findExpeditionWithRelations($expeditionId);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        $workflowOptions = $this->expeditionService->getWorkflowSelect();

        $subjectIds = $this->expeditionService->getSubjectIdsByExpeditionId($expeditionId)->toArray();

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => $subjectIds,
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.edit', [$expedition->project->id, $expedition->id]),
            'exportUrl'  => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'checkbox'   => $expedition->workflowManager === null,
            'route'      => 'edit', // used for export
        ]);

        return \View::make('admin.expedition.edit', compact('expedition', 'workflowOptions'));
    }

    /**
     * Update expedition.
     *
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ExpeditionFormRequest $request, $projectId, $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        try {
            // If expedition is completed and unlocked, this is a first change. If workflow id
            $expedition = $this->expeditionService->updateForGeoLocate($expeditionId, $request);

            $this->expeditionService->setSubjectIds($request->get('subject-ids'));
            $this->expeditionService->updateSubjects($expedition);
            $this->expeditionService->syncActors($expedition);

            // Success!
            \Flash::success(t('Record was updated successfully.'));

            return \Redirect::route('admin.expeditions.show', [$project->id, $expeditionId]);
        } catch (Exception $e) {
            \Flash::error(t('An error occurred when saving record.'));

            return \Redirect::route('admin.expeditions.edit', [$projectId, $expeditionId]);
        }
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId, $expeditionId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return \Redirect::route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionService->findExpeditionWithRelations($expeditionId);

            if (isset($expedition->workflowManager) || isset($expedition->panoptesProject)) {
                \Flash::error(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            DeleteExpedition::dispatch(Auth::user(), $expedition);

            \Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes. You will receive an email when complete.'));

            return \Redirect::route('admin.projects.show', [$projectId]);
        } catch (Exception $e) {
            \Flash::error(t('record.record_delete_error'));

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Sort expedition admin page.
     *
     * @return \Illuminate\Contracts\View\View|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $user = Auth::user();

        $type = \Request::get('type');
        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projectId = \Request::get('id');

        [$active, $completed] = $this->expeditionService->getAdminIndex($user->id, $sort, $order, $projectId)->partition(function ($expedition) {
            return $expedition->completed === 0;
        });

        $expeditions = $type === 'active' ? $active : $completed;

        return \View::make('admin.expedition.partials.expedition', compact('expeditions'));
    }
}
