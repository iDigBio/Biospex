<?php

namespace App\Http\Controllers\Admin;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpeditionFormRequest;
use App\Jobs\DeleteExpedition;
use App\Jobs\OcrCreateJob;
use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\ExpeditionStat;
use App\Repositories\Interfaces\NfnWorkflow;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\WorkflowManager;
use Artisan;
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
     * @var \App\Repositories\Interfaces\NfnWorkflow
     */
    private $nfnWorkflowContract;

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
     * @param \App\Repositories\Interfaces\NfnWorkflow $nfnWorkflowContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Repositories\Interfaces\ExpeditionStat $expeditionStatContract
     * @param \App\Repositories\Interfaces\WorkflowManager $workflowManagerContract
     */
    public function __construct(
        Expedition $expeditionContract,
        Project $projectContract,
        NfnWorkflow $nfnWorkflowContract,
        Subject $subjectContract,
        ExpeditionStat $expeditionStatContract,
        WorkflowManager $workflowManagerContract
    ) {
        $this->expeditionContract = $expeditionContract;
        $this->projectContract = $projectContract;
        $this->nfnWorkflowContract = $nfnWorkflowContract;
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

        list($expeditions, $expeditionsCompleted) = $results->partition(function ($expedition) {
            return $expedition->stat->percent_completed < '100.00';
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

        list($active, $completed) = $this->expeditionContract->getExpeditionAdminIndex($user->id, $sort, $order, $projectId)->partition(function (
                $expedition
            ) {
                return $expedition->stat->percent_completed < '100.00';
            });

        $expeditions = $type === 'active' ? $active : $completed;

        return view('admin.expedition.partials.expedition', compact('expeditions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function create($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'loadUrl'      => route('admin.grids.load', [$projectId]),
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
            FlashHelper::success(trans('messages.record_created'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
        }

        FlashHelper::error(trans('messages.record_save_error'));

        return redirect()->route('admin.projects.show', [$project->id]);
    }

    /**
     * Display the specified resource
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId)
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

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'loadUrl'      => route('admin.grids.load', [$projectId]),
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function clone($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('create', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'loadUrl'      => route('admin.grids.load', [$projectId]),
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat',
            'subjects',
            'nfnWorkflow',
        ];

        $expedition = $this->expeditionContract->findWith($expeditionId, $relations);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $subjectIds = $expedition->subjects->pluck('_id');

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => $subjectIds,
            'maxSubjects'  => config('config.expedition_size'),
            'loadUrl'      => route('admin.grids.load', [$projectId]),
            'gridUrl'      => route('admin.grids.edit', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('admin.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => $expedition->workflowManager === null,
            'explore'      => false,
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

            if ($request->filled('workflow')) {
                $values = [
                    'project_id'    => $project->id,
                    'expedition_id' => $expedition->id,
                    'workflow'      => $request->get('workflow'),
                ];

                $nfnWorkflow = $this->nfnWorkflowContract->updateOrCreate(['expedition_id' => $expedition->id], $values);

                UpdateNfnWorkflowJob::dispatch($nfnWorkflow);
            }

            // If process already in place, do not update subjects.
            $workflowManager = $this->workflowManagerContract->findBy('expedition_id', $expedition->id);
            if ($workflowManager === null) {
                $expedition->load('subjects');
                $subjectIds = $request->get('subject-ids') === null ? [] : explode(',', $request->get('subject-ids'));
                $count = count($subjectIds);
                $this->subjectContract->detachSubjects($expedition->subjects, $expedition->id);
                $expedition->subjects()->attach($subjectIds);

                $values = [
                    'local_subject_count' => $count,
                ];

                $this->expeditionStatContract->updateOrCreate(['expedition_id' => $expedition->id], $values);
            }

            // Success!
            FlashHelper::success(trans('messages.record_updated'));

            return redirect()->route('admin.expeditions.show', [$project->id, $expedition->id]);
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.record_save_error'));

            return redirect()->route('admin.expeditions.edit', [$project->id, $expedition->id]);
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

            Artisan::call('workflow:manage --expeditionId='.$expeditionId);

            FlashHelper::success(trans('messages.expedition_process_success'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.expedition_process_error', ['error' => $e->getMessage()]));

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

        FlashHelper::success(__('OCR processing has been submitted. It may take some time before appearing in the Processes menu. You will be notified by email when the process is complete.'));

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
            FlashHelper::error(trans('messages.process_no_exists'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerContract->update(['stopped' => 1], $workflow->id);
        FlashHelper::success(trans('messages.process_stopped'));

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
                'nfnWorkflow',
                'downloads',
                'workflowManager',
            ]);

            if (isset($expedition->workflowManager) || isset($expedition->nfnWorkflow)) {
                FlashHelper::error(trans('messages.expedition_process_exists'));

                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            DeleteExpedition::dispatch($expedition);

            FlashHelper::success(trans('messages.record_deleted'));

            return redirect()->route('admin.projects.index');
        } catch (\Exception $e) {
            FlashHelper::error(trans('record.record_delete_error'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
