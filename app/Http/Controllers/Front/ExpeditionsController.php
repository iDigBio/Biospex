<?php namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Config\Repository as Config;
use Biospex\Http\Requests\ExpeditionFormRequest;
use Biospex\Services\Common\PermissionService;
use Biospex\Repositories\Contracts\Expedition;
use Biospex\Repositories\Contracts\Project;
use Biospex\Repositories\Contracts\Subject;
use Biospex\Repositories\Contracts\WorkflowManager;
use Illuminate\Support\Facades\Artisan;
use Exception;

class ExpeditionsController extends Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Expedition
     */
    protected $expedition;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Instantiate a new ExpeditionsController.
     *
     * @param Request $request
     * @param PermissionService $permissionService
     * @param Expedition $expedition
     * @param Project $project
     * @param Subject $subject
     * @param WorkflowManager $workflowManager
     * @param Queue $queue
     * @param Config $config
     * @internal param ExpeditionForm $expeditionForm
     * @internal param OcrQueueInterface $ocr
     * @internal param Sentry $sentry
     * @internal param OcrProcess $ocrProcess
     * @internal param DarwinCoreCsvImport $csv
     * @internal param DarwinCore $process
     */
    public function __construct(
        Request $request,
        PermissionService $permissionService,
        Expedition $expedition,
        Project $project,
        Subject $subject,
        WorkflowManager $workflowManager,
        Queue $queue,
        Config $config
    ) {
        $this->request = $request;
        $this->permissionService = $permissionService;
        $this->expedition = $expedition;
        $this->project = $project;
        $this->subject = $subject;
        $this->workflowManager = $workflowManager;
        $this->queue = $queue;
        $this->config = $config;
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        if ( ! $this->request->ajax()) {
            return redirect()->route('projects.get.read', [$id]);
        }

        $user = $this->request->user();
        $project = $this->project->findWith($id, ['expeditions.actors', 'expeditions.stat']);

        return view('front.expeditions.index', compact('project', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function create($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }
;
        return view('front.expeditions.create', compact('project'));
    }

    /**
     * Store new expedition
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request, $projectId)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }

        $expedition = $this->expedition->create($request->all());

        if ($expedition) {
            session_flash_push('success', trans('expeditions.expedition_created'));

            return redirect()->route('projects.expeditions.get.read', [$projectId, $expedition->id]);
        }

        session_flash_push('error', trans('expeditions.expedition_save_error'));
        return redirect()->route('projects.get.read', [$projectId]);
    }

    /**
     * Display the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function read($projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads', 'workflowManager']);

        return view('front.expeditions.read', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId, $expeditionId)
    {
        $user = $this->request->user();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$expedition->project, $expedition->project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }

        return view('front.expeditions.clone', compact('expedition'));
    }

    /**
     * Show the form for editing the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId)
    {
        $this->expedition->cached(false);
        $user = $this->request->user();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group.permissions', 'workflowManager', 'subjects']);

        if ( ! $this->permissionService->checkPermissions($user, [$expedition->project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $subjectIds = [];
        foreach ($expedition->subjects as $subject) {
            $subjectIds[] = $subject->_id;
        }

        $showCb = is_null($expedition->workflowManager) ? 0 : 1;
        $subjects = implode(',', $subjectIds);

        return view('front.expeditions.edit', compact('expedition', 'subjects', 'showCb', 'subjects'));
    }

    /**
     * Update expedition
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ExpeditionFormRequest $request, $projectId, $expeditionId)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $expedition = $this->expedition->update($this->request->all());

        if ($expedition) {
            // Success!
            session_flash_push('success', trans('expeditions.expedition_updated'));

            return redirect()->route('projects.expeditions.get.read', [$projectId, $expeditionId]);
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
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        try {
            $this->expedition->cached(true);
            $expedition = $this->expedition->findWith($expeditionId, ['project.workflow.actors', 'workflowManager']);

            if ( ! is_null($expedition->workflowManager)) {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            } else {
                foreach($expedition->project->workflow->actors as $actor) {
                    if ( ! $actor->private) {
                        $actors[$actor->id] = ['order' => $actor->pivot->order];
                    }
                }

                $expedition->actors()->sync($actors);
                $this->workflowManager->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expedition' => $expeditionId]);

            session_flash_push('success', trans('expeditions.expedition_process_success'));
        } catch (Exception $e) {
            session_flash_push('error', trans('expeditions.expedition_process_error', ['error' => $e->getMessage()]));
        }

        return redirect()->route('projects.expeditions.get.read', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ocr($projectId, $expeditionId)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $data = [
            'project_id' => (int) $projectId,
            'expedition_id' => (int) $expeditionId
        ];
        $this->queue->push('Biospex\Services\Queue\OcrProcessBuild', $data, $this->config->get('config.beanstalkd.ocr'));
        session_flash_push('success', trans('expeditions.ocr_process_success'));

        return redirect()->route('projects.expeditions.get.read', [$projectId, $expeditionId]);
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
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $workflow = $this->workflowManager->findByExpeditionId($expeditionId);

        if (is_null($workflow)) {
            session_flash_push('error', trans('expeditions.process_no_exists'));
        } else {
            $workflow->stopped = 1;
            $this->workflowManager->save($workflow);
            session_flash_push('success', trans('expeditions.process_stopped'));
        }

        return redirect()->route('projects.expeditions.get.read', [$projectId, $expeditionId]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId, $expeditionId)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->permissionService->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('projects.get.index');
        }

        $workflow = $this->workflowManager->findByExpeditionId($expeditionId);
        if ( ! is_null($workflow)) {
            session_flash_push('error', trans('expeditions.expedition_process_exists'));

            return redirect()->route('projects.expeditions.get.read', [$projectId, $expeditionId]);
        } else {
            try {
                $subjects = $this->subject->getSubjectIds($projectId, null, $expeditionId);
                $this->subject->detachSubjects($subjects, $expeditionId);
                $this->expedition->destroy($expeditionId);

                session_flash_push('success', trans('expeditions.expedition_deleted'));
            } catch (Exception $e) {
                session_flash_push('error', trans('expeditions.expedition_destroy_error'));
            }
        }

        return redirect()->route('projects.get.read', [$projectId]);
    }
}
