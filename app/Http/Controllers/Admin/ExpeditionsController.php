<?php
/**
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

use App\Services\Grid\JqGridJsonEncoder;
use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpeditionFormRequest;
use App\Jobs\DeleteExpedition;
use App\Jobs\OcrCreateJob;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\ExpeditionStat;
use App\Repositories\Interfaces\PanoptesProject;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\WorkflowManager;
use Artisan;
use Exception;
use Illuminate\Support\Facades\Auth;
use JavaScript;

class ExpeditionsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Repositories\Interfaces\PanoptesProject
     */
    private $panoptesProjectContract;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * @var \App\Repositories\Interfaces\ExpeditionStat
     */
    private $expeditionStatContract;

    /**
     * @var \App\Repositories\Interfaces\WorkflowManager
     */
    private $workflowManagerContract;

    /**
     * ExpeditionsController constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\PanoptesProject $panoptesProjectContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Repositories\Interfaces\ExpeditionStat $expeditionStatContract
     * @param \App\Repositories\Interfaces\WorkflowManager $workflowManagerContract
     */
    public function __construct(
        Expedition $expeditionContract,
        Project $projectContract,
        PanoptesProject $panoptesProjectContract,
        Subject $subjectContract,
        ExpeditionStat $expeditionStatContract,
        WorkflowManager $workflowManagerContract
    ) {
        $this->expeditionContract = $expeditionContract;
        $this->projectContract = $projectContract;
        $this->panoptesProjectContract = $panoptesProjectContract;
        $this->subjectContract = $subjectContract;
        $this->expeditionStatContract = $expeditionStatContract;
        $this->workflowManagerContract = $workflowManagerContract;
    }

    /**
     * Display all expeditions for user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $results = $this->expeditionContract->getExpeditionAdminIndex($user->id);

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
        ] = $this->expeditionContract->getExpeditionAdminIndex($user->id, $sort, $order, $projectId)->partition(function (
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
     * @param \App\Services\Grid\JqGridJsonEncoder $grid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create($projectId, JqGridJsonEncoder $grid)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId, request()->route()->getName());

        JavaScript::put([
            'model'        => $model,
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'gridUrl'      => route('admin.grids.create', [$project->id]),
            'exportUrl'    => '',
            'editUrl'      => route('admin.grids.delete', [$projectId]),
            'showCheckbox' => true,
            'explore'      => false,
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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $expedition = $this->expeditionContract->create($request->all());
        $subjects = $request->get('subject-ids') === null ? [] : explode(',', $request->get('subject-ids'));
        $count = count($subjects);
        $expedition->subjects()->sync($subjects);

        $values = [
            'local_subject_count' => $count,
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        if ($expedition) {
            Flash::success(t('Record was created successfully.'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
        }

        Flash::error(t('An error occurred when saving record.'));

        return redirect()->route('admin.projects.show', [$project->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridJsonEncoder $grid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId, JqGridJsonEncoder $grid)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat',
        ];

        $expedition = $this->expeditionContract->findWith($expeditionId, $relations);

        if (! $this->checkPermissions('readProject', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId, request()->route()->getName());

        JavaScript::put([
            'model'        => $model,
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'gridUrl'      => route('admin.grids.show', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'editUrl'      => route('admin.grids.delete', [$projectId]),
            'showCheckbox' => false,
            'explore'      => false,
        ]);

        $btnDisable = ($expedition->project->ocrQueue->isEmpty() || $expedition->stat->local_subject_count === 0);

        return view('admin.expedition.show', compact('expedition', 'btnDisable'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridJsonEncoder $grid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function clone($projectId, $expeditionId, JqGridJsonEncoder $grid)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('create', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId, request()->route()->getName());

        JavaScript::put([
            'model'        => $model,
            'projectId'    => $expedition->project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'gridUrl'      => route('admin.grids.create', [$expedition->project->id]),
            'exportUrl'    => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'editUrl'      => route('admin.grids.delete', [$projectId]),
            'showCheckbox' => true,
            'explore'      => false,
        ]);

        return view('admin.expedition.clone', compact('expedition'));
    }

    /**
     * Show the form for editing the specified resource
     *
     * @param $projectId
     * @param $expeditionId
     * @param \App\Services\Grid\JqGridJsonEncoder $grid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId, JqGridJsonEncoder $grid)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat',
            'panoptesProject',
        ];

        $expedition = $this->expeditionContract->findWith($expeditionId, $relations);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $subjectIds = $this->subjectContract->findSubjectsByExpeditionId((int) $expeditionId, ['_id'])->pluck('_id');

        $model = $grid->loadGridModel($projectId, request()->route()->getName());

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => $subjectIds,
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.edit', [$expedition->project->id, $expedition->id]),
            'delUrl'     => route('admin.expeditions.delete.subjects', [$expedition->project->id, $expedition->id]),
            'exportUrl'  => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'checkbox'   => $expedition->workflowManager === null,
            'explore'    => false,
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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionContract->update($request->all(), $expeditionId);

            if ($request->filled('panoptes_workflow_id')) {
                $attributes = $attributes = [
                    'project_id'    => $project->id,
                    'expedition_id' => $expedition->id,
                ];

                $values = [
                    'project_id'           => $project->id,
                    'expedition_id'        => $expedition->id,
                    'panoptes_workflow_id' => $request->get('panoptes_workflow_id'),
                ];

                $panoptesProject = $this->panoptesProjectContract->updateOrCreate($attributes, $values);

                PanoptesProjectUpdateJob::dispatch($panoptesProject);
            }

            // If process already in place, do not update subjects.
            $workflowManager = $this->workflowManagerContract->findBy('expedition_id', $expedition->id);
            if ($workflowManager === null) {
                $expedition->load('subjects');
                $subjectIds = $request->get('subject-ids') === null ? [] : explode(',', $request->get('subject-ids'));
                $count = count($subjectIds);

                $oldIds = collect($expedition->subjects->pluck('_id'));
                $newIds = collect($subjectIds);

                $detachIds = $oldIds->diff($newIds);
                $attachIds = $newIds->diff($oldIds);

                $this->subjectContract->detachSubjects($detachIds, $expedition->id);
                $this->subjectContract->attachSubjects($attachIds, $expedition->id);

                $values = [
                    'local_subject_count' => $count,
                ];

                $this->expeditionStatContract->updateOrCreate(['expedition_id' => $expedition->id], $values);
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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionContract->findWith($expeditionId, [
                'project.workflow.actors',
                'workflowManager',
            ]);

            if (null !== $expedition->workflowManager) {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            } else {
                $expedition->project->workflow->actors->reject(function ($actor) {
                    return $actor->private;
                })->each(function ($actor) use ($expedition) {
                    $expedition->actors()->sync([$actor->id => ['order' => $actor->pivot->order]], false);
                });

                $this->workflowManagerContract->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expeditionId' => $expeditionId]);

            Flash::success(t('The expedition has been added to the process queue.'));

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

        $project = $this->projectContract->findWith($projectId, ['group']);

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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $workflow = $this->workflowManagerContract->findBy('expedition_id', $expeditionId);

        if ($workflow === null) {
            Flash::error(t('Expedition has no processes at this time.'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerContract->update(['stopped' => 1], $workflow->id);
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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionContract->findWith($expeditionId, [
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

            return redirect()->route('admin.projects.index');
        } catch (Exception $e) {
            Flash::error(t('record.record_delete_error'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    public function deleteSubject(string $projectId, string $expeditionId)
    {
        return request()->all();
    }
}
