<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\BuildOcrBatches;
use App\Repositories\Contracts\OcrQueue;
use App\Repositories\Contracts\User;
use App\Http\Requests\ExpeditionFormRequest;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
     * @var Request
     */
    protected $request;


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
     * Display all expeditions for user.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * TODO: Fix query so it can be cached in normal fashion.
     */
    public function index()
    {
        $user = Request::user();
        $results = Cache::remember(md5(Request::url() . $user->id), 60, function () use($user)
        {
            return $this->expedition->getAllExpeditions($user->id);
        });

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
            return redirect()->route('web.projects.show', [$id]);
        }

        $user = Request::user();
        $project = $this->project->with(['expeditions.actors', 'expeditions.stat'])->find($id);

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
        $project = $this->project->with(['group.permissions'])->find($id);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }
;
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
        $user = Request::user();
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project, $project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }
        
        $expedition = $this->expedition->create($request->all());

        if ($expedition) {
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
        $expedition = $this->expedition->with(['project.group', 'downloads', 'workflowManager'])->find($expeditionId);

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
        $expedition = $this->expedition->with(['project.group.permissions'])->find($expeditionId);

        if ( ! $this->checkPermissions($user, [$expedition->project, $expedition->project->group], 'create'))
        {
            return redirect()->route('web.projects.index');
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
        $user = Request::user();
        $expedition = $this->expedition->skipCache()->with(['project.group.permissions', 'workflowManager', 'subjects'])->find($expeditionId);

        if ( ! $this->checkPermissions($user, [$expedition->project], 'update'))
        {
            return redirect()->route('web.projects.index');
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
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $expedition = $this->expedition->update($request->all(), $expeditionId);

        if ($expedition) {
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
        $user = Request::user();
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        try {
            $expedition = $this->expedition->skipCache(['project.workflow.actors', 'workflowManager'])->with()->find($expeditionId);

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

        return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR.
     * 
     * @param OcrQueue $queue
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function ocr(OcrQueue $queue, $projectId, $expeditionId)
    {
        $user = Request::user();

        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $queueCheck = $queue->skipCache()->where(['project_id' => $projectId])->first();
        
        if ($queueCheck === null)
        {
            $this->dispatch((new BuildOcrBatches($project->id, $expeditionId))->onQueue(Config::get('config.beanstalkd.ocr')));

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
        $user = Request::user();
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $workflow = $this->workflowManager->where(['expedition_id' => $expeditionId])->get();

        if ($workflow === null) {
            session_flash_push('error', trans('expeditions.process_no_exists'));
        } else {
            $workflow->stopped = 1;
            $this->workflowManager->save($workflow);
            session_flash_push('success', trans('expeditions.process_stopped'));
        }

        return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
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
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $workflow = $this->workflowManager->where(['expedition_id' => $expeditionId])->first();
        if ( $workflow !== null) {
            session_flash_push('error', trans('expeditions.expedition_process_exists'));

            return redirect()->route('web.expeditions.show', [$projectId, $expeditionId]);
        } else {
            try {
                $subjects = $this->subject->getSubjectIds($projectId, null, $expeditionId);
                $this->subject->detachSubjects($subjects, $expeditionId);
                $this->expedition->delete($expeditionId);

                session_flash_push('success', trans('expeditions.expedition_deleted'));
            } catch (Exception $e) {
                session_flash_push('error', trans('expeditions.expedition_destroy_error'));
            }
        }

        return redirect()->route('web.projects.show', [$projectId]);
    }
}
