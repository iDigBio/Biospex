<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\BuildOcrBatches;
use App\Repositories\Contracts\User;
use App\Http\Requests\ExpeditionFormRequest;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Exception;

class ExpeditionsController extends Controller
{
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
     * @var User
     */
    protected $user;

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;


    /**
     * ExpeditionsController constructor.
     *
     * @param Expedition $expedition
     * @param Project $project
     * @param Subject $subject
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        Expedition $expedition,
        Project $project,
        Subject $subject,
        WorkflowManager $workflowManager
    ) {
        $this->expedition = $expedition;
        $this->project = $project;
        $this->subject = $subject;
        $this->workflowManager = $workflowManager;
    }

    /**
     * Display all expeditions for user
     * @param User $userContract
     * @return mixed
     */
    public function index(User $userContract)
    {
        $user = $userContract->find(Request::user()->id);
        $results = $this->expedition->getAllExpeditions(Request::user()->id);

        return view('frontend.expeditions.index', compact('results', 'user'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function ajax($id)
    {
        if ( ! Request::ajax()) {
            return redirect()->route('projects.get.show', [$id]);
        }

        $user = Request::user();
        $project = $this->project->findWith($id, ['expeditions.actors', 'expeditions.stat']);

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
        $user = Request::user();
        $project = $this->project->findWith($id, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }
;
        return view('frontend.expeditions.create', compact('project'));
    }

    /**
     * Store new expedition
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request, $projectId)
    {
        $user = Request::user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }

        $expedition = $this->expedition->create($request->all());

        if ($expedition) {
            session_flash_push('success', trans('expeditions.expedition_created'));

            return redirect()->route('projects.expeditions.get.show', [$projectId, $expedition->id]);
        }

        session_flash_push('error', trans('expeditions.expedition_save_error'));
        return redirect()->route('projects.get.show', [$projectId]);
    }

    /**
     * Display the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads', 'workflowManager']);

        return view('frontend.expeditions.show', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId, $expeditionId)
    {
        $user = Request::user();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group.permissions']);

        if ( ! $this->checkPermissions($user, [$expedition->project, $expedition->project->group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }

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
        $this->expedition->cached(false);
        $user = Request::user();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group.permissions', 'workflowManager', 'subjects']);

        if ( ! $this->checkPermissions($user, [$expedition->project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $subjectIds = [];
        foreach ($expedition->subjects as $subject) {
            $subjectIds[] = $subject->_id;
        }

        $showCb = $expedition->workflowManager === null ? 0 : 1;
        $subjects = implode(',', $subjectIds);

        return view('frontend.expeditions.edit', compact('expedition', 'subjects', 'showCb', 'subjects'));
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
        $user = Request::user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $expedition = $this->expedition->update(Request::all());

        if ($expedition) {
            // Success!
            session_flash_push('success', trans('expeditions.expedition_updated'));

            return redirect()->route('projects.expeditions.get.show', [$projectId, $expeditionId]);
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
        $user = Request::user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
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

        return redirect()->route('projects.expeditions.get.show', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function ocr($projectId, $expeditionId)
    {
        $user = Request::user();

        $project = $this->project->findWith($projectId, ['group.permissions', 'workflow.actors']);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $this->dispatch((new BuildOcrBatches($project, $expeditionId))->onQueue(Config::get('config.beanstalkd.ocr')));        
        
        session_flash_push('success', trans('expeditions.ocr_process_success'));

        return redirect()->route('projects.expeditions.get.show', [$projectId, $expeditionId]);
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
        $user = Request::user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
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

        return redirect()->route('projects.expeditions.get.show', [$projectId, $expeditionId]);
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
        $user = Request::user();
        $project = $this->project->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('projects.get.index');
        }

        $workflow = $this->workflowManager->findByExpeditionId($expeditionId);
        if ( $workflow !== null) {
            session_flash_push('error', trans('expeditions.expedition_process_exists'));

            return redirect()->route('projects.expeditions.get.show', [$projectId, $expeditionId]);
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

        return redirect()->route('projects.get.show', [$projectId]);
    }
}
