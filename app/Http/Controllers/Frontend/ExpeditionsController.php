<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\BiospexException;
use App\Http\Controllers\Controller;
use App\Jobs\BuildOcrBatchesJob;
use App\Jobs\UpdateNfnWorkflowJob;
use App\Http\Requests\ExpeditionFormRequest;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use File;
use Illuminate\Support\Facades\Artisan;
use App\Exceptions\Handler;
use JavaScript;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\WorkflowManagerContract;
use App\Repositories\Contracts\OcrQueueContract;
use App\Repositories\Contracts\UserContract;

class ExpeditionsController extends Controller
{

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * @var SubjectContract
     */
    public $subjectContract;

    /**
     * @var WorkflowManagerContract
     */
    public $workflowManagerContract;

    /**
     * @var Handler
     */
    public $handler;

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;


    /**
     * ExpeditionsController constructor.
     *
     * @param ExpeditionContract $expeditionContract
     * @param ProjectContract $projectContract
     * @param SubjectContract $subjectContract
     * @param WorkflowManagerContract $workflowManagerContract
     * @param Handler $handler
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        ProjectContract $projectContract,
        SubjectContract $subjectContract,
        WorkflowManagerContract $workflowManagerContract,
        Handler $handler
    )
    {
        $this->projectContract = $projectContract;
        $this->subjectContract = $subjectContract;
        $this->workflowManagerContract = $workflowManagerContract;
        $this->expeditionContract = $expeditionContract;
        $this->handler = $handler;
    }

    /**
     * Display all expeditions for user.
     *
     * @param UserContract $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UserContract $userContract)
    {
        $user = $userContract->with('profile')->find(request()->user()->id);

        $relations = ['stat', 'downloads', 'actors', 'project.group'];
        $expeditions = $this->expeditionContract->expeditionsByUserId($user->id, $relations);

        return view('frontend.expeditions.index', compact('expeditions', 'user'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param UserContract $userContract
     * @param $id
     * @return \Illuminate\View\View
     */
    public function ajax(UserContract $userContract, $id)
    {
        if ( ! request('ajax'))
        {
            return redirect()->route('web.projects.show', [$id]);
        }

        $user = $userContract->with('profile')->find(request()->user()->id);

        $project = $this->projectContract->with(['expeditions.actors', 'expeditions.stat'])->find($id);

        return view('frontend.expeditions.ajax', compact('project', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function create($id)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($id);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.create', [$project->id]),
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
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }

        $expedition = $this->expeditionContract->createExpedition($request->all());

        if ($expedition)
        {
            session_flash_push('success', trans('expeditions.expedition_created'));

            return redirect()->route('web.expeditions.show', [$projectId, $expedition->id]);
        }

        session_flash_push('error', trans('expeditions.expedition_save_error'));
        return redirect()->route('web.projects.show', [$projectId]);
    }

    /**
     * Display the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->with([
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat'])
            ->find($expeditionId);

        $btnDisable = ($expedition->project->ocrQueue->isEmpty() || $expedition->stat->subject_count === 0);

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.show', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => false,
            'explore'      => false
        ]);

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
        $user = request()->user();
        $expedition = $this->expeditionContract->with(['project.group.permissions'])->find($expeditionId);

        if ( ! $this->checkPermissions($user, [$expedition->project, $expedition->project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.create', [$expedition->project->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
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
        $user = request()->user();
        $expedition = $this->expeditionContract->setCacheLifetime(0)
            ->with([
            'project.group.permissions',
            'workflowManager',
            'subjects',
            'nfnWorkflow'
        ])->find($expeditionId);

        if ( ! $this->checkPermissions($user, [$expedition->project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $subjectIds = [];
        foreach ($expedition->subjects as $subject)
        {
            $subjectIds[] = $subject->_id;
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => $subjectIds,
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.edit', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
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
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $expedition = $this->expeditionContract->updateExpedition($expeditionId, $request->all());

        if ($expedition)
        {
            if (null !== $expedition->nfnWorkflow)
            {
                $this->dispatch((new UpdateNfnWorkflowJob($expedition->nfnWorkflow))
                    ->onQueue(config('config.beanstalkd.workflow')));
            }

            // Success!
            session_flash_push('success', trans('expeditions.expedition_updated'));

            return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
        }

        session_flash_push('error', trans('expeditions.expedition_save_error'));

        return redirect()->route('projects.expeditions.edit', [$projectId, $expeditionId]);
    }

    /**
     * Start processing expedition actors
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        try
        {
            $expedition = $this->expeditionContract->setCacheLifetime(0)
                ->with(['project.workflow.actors', 'workflowManager'])
                ->find($expeditionId);

            if (null !== $expedition->workflowManager)
            {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            }
            else
            {
                $expedition->project->workflow->actors->reject(function($actor) {
                    return $actor->private;
                })->each(function($actor) use ($expedition){
                    $expedition->actors()->syncWithoutDetaching([$actor->id => ['order' => $actor->pivot->order]]);
                });

                $this->workflowManagerContract->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expedition' => $expeditionId]);

            session_flash_push('success', trans('expeditions.expedition_process_success'));
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);
            session_flash_push('error', trans('expeditions.expedition_process_error', ['error' => $e->getMessage()]));
        }

        return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR.
     *
     * TODO Add ocr actor to actor_expedition table. Check if already exists and set to 0.
     * @param OcrQueueContract $ocrQueueContract
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function ocr(OcrQueueContract $ocrQueueContract, $projectId, $expeditionId)
    {
        $user = request()->user();

        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $queueCheck = $ocrQueueContract->setCacheLifetime(0)
            ->where('project_id'. '=', $projectId)->findFirst();

        if ($queueCheck === null)
        {
            $this->dispatch((new BuildOcrBatchesJob($project->id, $expeditionId))->onQueue(config('config.beanstalkd.ocr')));

            session_flash_push('success', trans('expeditions.ocr_process_success'));
        }
        else
        {
            session_flash_push('warning', trans('expeditions.ocr_process_error'));
        }

        return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
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
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $workflow = $this->workflowManagerContract->where('expedition_id', '=', $expeditionId)->findFirst();

        if ($workflow === null)
        {
            session_flash_push('error', trans('expeditions.process_no_exists'));
        }
        else
        {
            $workflow->stopped = 1;
            $this->workflowManagerContract->update($workflow->id, ['stopped' => 1]);
            session_flash_push('success', trans('expeditions.process_stopped'));
        }

        return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param ModelDeleteService $service
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $projectId, $expeditionId)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $result = $service->deleteExpedition($expeditionId);

        $result ?
            session_flash_push('success', trans('expeditions.expedition_deleted')) :
            session_flash_push('error', trans('expeditions.expedition_delete_error'));

        return $result ?
            redirect()->route('web.projects.show', [$projectId]) :
            redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);

    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param ModelDestroyService $service
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $projectId, $expeditionId)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $result = $service->destroyExpedition($expeditionId);

        $result ? session_flash_push('success', trans('expeditions.expedition_destroyed')) :
            session_flash_push('error', trans('expeditions.expedition_destroy_error'));

        return $result ?
            redirect()->route('web.projects.show', [$projectId]) :
            redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);

    }

    /**
     * Restore deleted expedition.
     *
     * @param ModelRestoreService $service
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $projectId, $expeditionId)
    {
        $service->restoreExpedition($expeditionId) ?
            session_flash_push('success', trans('expeditions.expedition_restore')) :
            session_flash_push('error', trans('expeditions.expedition_restore_error'));

        return redirect()->route('web.projects.show', [$projectId]);
    }

    /**
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function summary($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->find($expeditionId);

        $file = 24 . '.html';

        if (File::exists(config('config.classifications_summary') . '/' . $file))
        {
            $contents = File::get(config('config.classifications_summary') . '/' . $file);
            return view('frontend.summary', compact('contents'));
        }

        $contents = trans('errors.missing_summary', ['title' => $expedition->title]);
        return view('frontend.summary', compact('contents'));
    }
}
