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

use App\Services\Grid\JqGridEncoder;
use App\Services\Model\SubjectService;
use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpeditionFormRequest;
use App\Jobs\DeleteExpedition;
use App\Jobs\OcrCreateJob;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Services\Model\ExpeditionService;
use App\Services\Model\ExpeditionStatService;
use App\Services\Model\PanoptesProjectService;
use App\Services\Model\ProjectService;
use App\Services\Model\WorkflowManagerService;
use Artisan;
use Exception;
use Illuminate\Support\Facades\Auth;
use JavaScript;

/**
 * Class ExpeditionController
 *
 * @package App\Http\Controllers\Admin
 */
class ExpeditionController extends Controller
{
    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * @var \App\Services\Model\ProjectService
     */
    private $projectService;

    /**
     * @var \App\Services\Model\PanoptesProjectService
     */
    private $panoptesProjectService;

    /**
     * @var \App\Services\Model\ExpeditionStatService
     */
    private $expeditionStatService;

    /**
     * @var \App\Services\Model\WorkflowManagerService
     */
    private $workflowManagerService;

    /**
     * @var \App\Services\Model\SubjectService
     */
    private $subjectService;

    /**
     * ExpeditionController constructor.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Model\ProjectService $projectService
     * @param \App\Services\Model\PanoptesProjectService $panoptesProjectService
     * @param \App\Services\Model\SubjectService $subjectService
     * @param \App\Services\Model\ExpeditionStatService $expeditionStatService
     * @param \App\Services\Model\WorkflowManagerService $workflowManagerService
     */
    public function __construct(
        ExpeditionService $expeditionService,
        ProjectService $projectService,
        PanoptesProjectService $panoptesProjectService,
        SubjectService $subjectService,
        ExpeditionStatService $expeditionStatService,
        WorkflowManagerService $workflowManagerService
    ) {
        $this->expeditionService = $expeditionService;
        $this->projectService = $projectService;
        $this->panoptesProjectService = $panoptesProjectService;
        $this->expeditionStatService = $expeditionStatService;
        $this->workflowManagerService = $workflowManagerService;
        $this->subjectService = $subjectService;
    }

    /**
     * Display all expeditions for user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $results = $this->expeditionService->getExpeditionAdminIndex($user->id);

        [$expeditions, $expeditionsCompleted] = $results->partition(function ($expedition) {
            return ($expedition->nfnActor === null || $expedition->nfnActor->pivot->completed === 0);
        });

        return view('admin.expedition.index', compact('expeditions', 'expeditionsCompleted'));
    }

    /**
     * Sort expedition admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function sort()
    {
        if (! request()->ajax()) {
            return null;
        }

        $user = Auth::user();

        $type = request()->get('type');
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        [
            $active,
            $completed,
        ] = $this->expeditionService->getExpeditionAdminIndex($user->id, $sort, $order, $projectId)->partition(function (
                $expedition
            ) {
                return ($expedition->nfnActor === null || $expedition->nfnActor->pivot->completed === 0);
            });

        $expeditions = $type === 'active' ? $active : $completed;

        return view('admin.expedition.partials.expedition', compact('expeditions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $projectId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create($projectId, JqGridEncoder $grid)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

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

        return view('admin.expedition.create', compact('project'));
    }

    /**
     * Store new expedition.
     *
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request, $projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group', 'workflow.actors']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $expedition = $this->expeditionService->create($request->all());
        if (! $expedition) {
            Flash::error(t('An error occurred when saving record.'));

            return redirect()->route('admin.projects.show', [$project->id]);
        }

        $subjects = $request->get('subject-ids') === null ? [] : explode(',', $request->get('subject-ids'));
        $count = count($subjects);
        $expedition->subjects()->sync($subjects);

        $values = [
            'local_subject_count' => $count,
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        $project->workflow->actors->reject(function ($actor) {
            return $actor->private;
        })->each(function ($actor) use ($expedition, $count) {
            $sync = [
                $actor->id => ['order' => $actor->pivot->order, 'state' => 0, 'total' => $count],
            ];
            $expedition->actors()->sync($sync, false);
        });

        Flash::success(t('Record was created successfully.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId, JqGridEncoder $grid)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat',
        ];

        $expedition = $this->expeditionService->findWith($expeditionId, $relations);

        if (! $this->checkPermissions('readProject', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
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

        return view('admin.expedition.show', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function clone($projectId, $expeditionId, JqGridEncoder $grid)
    {
        $expedition = $this->expeditionService->findWith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('create', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

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

        return view('admin.expedition.clone', compact('expedition'));
    }

    /**
     * Show the form for editing the specified resource
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId, JqGridEncoder $grid)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat',
            'panoptesProject',
        ];

        $expedition = $this->expeditionService->findWith($expeditionId, $relations);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $subjectIds = $this->subjectService->findByExpeditionId((int) $expeditionId, ['_id'])->pluck('_id');

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

        return view('admin.expedition.edit', compact('expedition'));
    }

    /**
     * Update expedition.
     *
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ExpeditionFormRequest $request, $projectId, $expeditionId)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionService->update($request->all(), $expeditionId);

            if ($request->filled('panoptes_workflow_id')) {
                $attributes = [
                    'project_id'    => $project->id,
                    'expedition_id' => $expedition->id,
                ];

                $values = [
                    'project_id'           => $project->id,
                    'expedition_id'        => $expedition->id,
                    'panoptes_workflow_id' => $request->get('panoptes_workflow_id'),
                ];

                $panoptesProject = $this->panoptesProjectService->updateOrCreate($attributes, $values);

                PanoptesProjectUpdateJob::dispatch($panoptesProject);
            }

            // If process already in place, do not update subjects.
            $workflowManager = $this->workflowManagerService->findBy('expedition_id', $expedition->id);
            if ($workflowManager === null) {
                $expedition->load('subjects');
                $subjectIds = $request->get('subject-ids') === null ? [] : explode(',', $request->get('subject-ids'));
                $count = count($subjectIds);

                $oldIds = collect($expedition->subjects->pluck('_id'));
                $newIds = collect($subjectIds);

                $detachIds = $oldIds->diff($newIds);
                $attachIds = $newIds->diff($oldIds);

                $this->subjectService->detachSubjects($detachIds, $expedition->id);
                $this->subjectService->attachSubjects($attachIds, $expedition->id);

                $values = [
                    'local_subject_count' => $count,
                ];

                $this->expeditionStatService->updateOrCreate(['expedition_id' => $expedition->id], $values);
            }

            // Success!
            Flash::success(t('Record was updated successfully.'));

            return redirect()->route('admin.expeditions.show', [$project->id, $expedition->id]);
        } catch (Exception $e) {
            Flash::error(t('An error occurred when saving record.'));

            return redirect()->route('admin.expeditions.edit', [$projectId, $expeditionId]);
        }
    }

    /**
     * Start processing expedition actors
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionService->findWith($expeditionId, [
                'project.workflow.actors',
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
                $expedition->project->workflow->actors->reject(function ($actor) {
                    return $actor->private;
                })->each(function ($actor) use ($expedition) {
                    $sync = [
                        $actor->id => ['order' => $actor->pivot->order, 'state' => 1],
                    ];
                    $expedition->actors()->sync($sync, false);
                });

                $this->workflowManagerService->create(['expedition_id' => $expeditionId]);
                $message = t('The expedition has been added to the process queue.');
            }

            Flash::success($message);

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (Exception $e) {
            Flash::error(t('An error occurred when trying to process the expedition: %s', $e->getMessage()));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function ocr($projectId, $expeditionId)
    {

        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        OcrCreateJob::dispatch($projectId, $expeditionId);

        Flash::success(t('OCR processing has been submitted. It may take some time before appearing in the Processes menu. You will be notified by email when the process is complete.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Stop a expedition process.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop($projectId, $expeditionId)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $workflow = $this->workflowManagerService->findBy('expedition_id', $expeditionId);

        if ($workflow === null) {
            Flash::error(t('Expedition has no processes at this time.'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerService->update(['stopped' => 1], $workflow->id);
        Flash::success(t('Expedition process has been stopped locally. This does not stop any processing occurring on remote sites.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId, $expeditionId)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionService->findWith($expeditionId, [
                'panoptesProject',
                'downloads',
                'workflowManager',
            ]);

            if (isset($expedition->workflowManager) || isset($expedition->panoptesProject)) {
                Flash::error(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            DeleteExpedition::dispatch(Auth::user(), $expedition);

            Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes. You will receive an email when complete.'));

            return redirect()->route('admin.projects.show', [$projectId]);
        } catch (Exception $e) {
            Flash::error(t('record.record_delete_error'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
