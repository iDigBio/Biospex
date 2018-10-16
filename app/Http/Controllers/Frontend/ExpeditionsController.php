<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
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
    )
    {
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
        $user->load('profile');
        $relations = ['stat', 'downloads', 'actors', 'project.group'];

        $expeditions = $this->expeditionContract->expeditionsByUserId($user->id, $relations);

        return view('frontend.expeditions.index', compact('expeditions', 'user'));
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

        if ( ! $this->checkPermissions('createProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('webauth.grids.create', [$project->id]),
            'exportUrl'    => '',
            'showCheckbox' => true,
            'explore'      => false
        ]);

        return view('frontend.expeditions.create', compact('project'));
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

        if ( ! $this->checkPermissions('createProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        $expedition = $this->expeditionContract->create($request->all());
        $subjects = $request->get('subjectIds') === null ? [] : explode(',', $request->get('subjectIds'));
        $count = count($subjects);
        $expedition->subjects()->sync($subjects);

        $values = [
            'local_subject_count' => $count
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        if ($expedition)
        {
            Flash::success(trans('messages.record_created'));

            return redirect()->route('webauth.expeditions.show', [$projectId, $expedition->id]);
        }

        Flash::error(trans('messages.record_save_error'));
        return redirect()->route('webauth.projects.show', [$projectId]);
    }

    /**
     * Display the specified resource
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
            'stat'
        ];

        $expedition = $this->expeditionContract->findWith($expeditionId, $relations);

        if ( ! $this->checkPermissions('readProject', $expedition->project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('webauth.grids.show', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('webauth.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => false,
            'explore'      => false
        ]);

        $btnDisable = ($expedition->project->ocrQueue->isEmpty() || $expedition->stat->local_subject_count === 0);

        return view('frontend.expeditions.show', compact('expedition', 'btnDisable'));
    }

    /**
     * Clone an existing expedition
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group']);

        if ( ! $this->checkPermissions('create', $expedition->project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('webauth.grids.create', [$expedition->project->id]),
            'exportUrl'    => route('webauth.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => true,
            'explore'      => false
        ]);

        return view('frontend.expeditions.clone', compact('expedition'));
    }

    /**
     * Show the form for editing the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId)
    {
        $relations = [
            'project.group',
            'workflowManager',
            'subjects',
            'nfnWorkflow'
        ];

        $expedition = $this->expeditionContract->findWith($expeditionId, $relations);

        if ( ! $this->checkPermissions('updateProject', $expedition->project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        $subjectIds = $expedition->subjects->pluck('_id');

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => $subjectIds,
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('webauth.grids.edit', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('webauth.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => $expedition->workflowManager === null,
            'explore'      => false
        ]);

        return view('frontend.expeditions.edit', compact('expedition'));
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

        if ( ! $this->checkPermissions('updateProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        try {
            $expedition = $this->expeditionContract->update($request->all(), $expeditionId);

            if ($request->filled('workflow')) {
                $values = [
                    'project_id'    => $request->input('project_id'),
                    'expedition_id' => $expedition->id,
                    'workflow'      => $request->get('workflow')
                ];

                $nfnWorkflow = $this->nfnWorkflowContract->updateOrCreate(['expedition_id' => $expedition->id], $values);

                UpdateNfnWorkflowJob::dispatch($nfnWorkflow);
            }

            // If process already in place, do not update subjects.
            $workflowManager = $this->workflowManagerContract->findBy('expedition_id', $expedition->id);
            if ($workflowManager === null) {
                $expedition->load('subjects');
                $subjectIds = $request->get('subjectIds') === null ? [] : explode(',', $request->get('subjectIds'));
                $count = count($subjectIds);
                $this->subjectContract->detachSubjects($expedition->subjects, $expedition->id);
                $expedition->subjects()->attach($subjectIds);

                $values = [
                    'local_subject_count' => $count
                ];

                $this->expeditionStatContract->updateOrCreate(['expedition_id' => $expedition->id], $values);
            }

            // Success!
            Flash::success(trans('messages.record_updated'));

            return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
        }
        catch(\Exception $e)
        {
            Flash::error(trans('messages.record_save_error'));

            return redirect()->route('webauth.expeditions.edit', [$projectId, $expeditionId]);
        }
    }

    /**
     * Start processing expedition actors
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if ( ! $this->checkPermissions('updateProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        try
        {
            $expedition = $this->expeditionContract->findWith($expeditionId, ['project.workflow.actors', 'workflowManager']);

            if (null !== $expedition->workflowManager)
            {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            }
            else
            {
                $expedition->project->workflow->actors->reject(function ($actor) {
                    return $actor->private;
                })->each(function ($actor) use ($expedition) {
                    $expedition->actors()->syncWithoutDetaching([$actor->id => ['order' => $actor->pivot->order]]);
                });

                $this->workflowManagerContract->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expeditionId' => $expeditionId]);

            Flash::success(trans('messages.expedition_process_success'));
            return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
        }
        catch (\Exception $e)
        {
            Flash::error(trans('messages.expedition_process_error', ['error' => $e->getMessage()]));
            return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
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

        if ( ! $this->checkPermissions('updateProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        OcrCreateJob::dispatch($projectId, $expeditionId);

        Flash::success(trans('messages.ocr_process_success'));

        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
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

        if ( ! $this->checkPermissions('updateProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        $workflow = $this->workflowManagerContract->findBy('expedition_id', $expeditionId);

        if ($workflow === null)
        {
            Flash::error(trans('messages.process_no_exists'));
            return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerContract->update(['stopped' => 1], $workflow->id);
        Flash::success(trans('messages.process_stopped'));
        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
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

        if ( ! $this->checkPermissions('isOwner', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        try
        {
            $expedition = $this->expeditionContract->findWith($expeditionId, ['nfnWorkflow', 'downloads', 'workflowManager']);

            if (isset($expedition->workflowManager) || isset($expedition->nfnWorkflow))
            {
                Flash::error(trans('messages.expedition_process_exists'));

                return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
            }

            DeleteExpedition::dispatch($expedition);

            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('webauth.projects.index');
        }
        catch (\Exception $e)
        {
            Flash::error(trans('record.record_delete_error'));

            return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
