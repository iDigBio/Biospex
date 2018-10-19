<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteProject;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\User;
use App\Http\Requests\ProjectFormRequest;
use App\Services\File\FileService;
use App\Services\Model\CommonVariables;
use App\Services\MongoDbService;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Model\CommonVariables
     */
    private $commonVariables;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * @var \App\Services\Model\OcrQueueService
     */
    private $ocrQueueService;

    /**
     * @var \App\Services\File\FileService
     */
    private $fileService;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\File\FileService $fileService
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\Model\CommonVariables $commonVariables
     */
    public function __construct(
        Group $groupContract,
        Project $projectContract,
        Expedition $expeditionContract,
        Subject $subjectContract,
        FileService $fileService,
        MongoDbService $mongoDbService,
        CommonVariables $commonVariables
    ) {
        $this->groupContract = $groupContract;
        $this->commonVariables = $commonVariables;
        $this->projectContract = $projectContract;
        $this->expeditionContract = $expeditionContract;
        $this->subjectContract = $subjectContract;
        $this->fileService = $fileService;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        /*
        $user = \Auth::user();
        $groups = $this->groupContract->getUserProjectListByGroup($user);

        if (! $groups->count()) {
            return redirect()->route('admin.home.welcome');
        }
        */

        return view('admin.project.index');
        //return view('admin.project.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $vars = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if ($vars) {
            return view('frontend.projects.create', $vars);
        }

        return redirect()->route('groups.create');
    }

    /**
     * Display the specified resource.
     *
     * @param User $userContract
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(User $userContract, $projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group', 'ocrQueue']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $user = $userContract->findWith(request()->user()->id, ['profile']);

        $expeditions = $this->expeditionContract->findExpeditionsByProjectIdWith($projectId, [
            'downloads',
            'actors',
            'stat',
        ]);

        return view('frontend.projects.show', compact('user', 'project', 'expeditions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $group = $this->groupContract->find($request->get('group_id'));

        if (! $this->checkPermissions('createProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectContract->create($request->all());

        if ($project) {
            $this->commonVariables->notifyActorContacts($project->id);

            Flash::success(trans('message.record_created'));

            return redirect()->route('admin.projects.show', [$project->id]);
        }

        Flash::error(trans('messages.record_save_error'));

        return redirect()->route('admin.projects.create')->withInput();
    }

    /**
     * Create duplicate project
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group', 'expeditions.workflowManager']);

        if (! $project) {
            Flash::error(trans('pages.project_repo_error'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $common = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if (! $common) {
            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return view('frontend.projects.clone', $variables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function edit($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group', 'nfnWorkflows', 'resources']);
        if (! $project) {
            Flash::error(trans('pages.project_repo_error'));

            return redirect()->route('admin.projects.index');
        }

        $workflowEmpty = ! isset($project->nfnWorkflows) || $project->nfnWorkflows->isEmpty();

        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $common = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if (! $common) {
            return redirect()->route('admin.projects.index');
        }

        $variables = array_merge($common, ['project' => $project, 'workflowEmpty' => $workflowEmpty]);

        return view('frontend.projects.edit', $variables);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @param $projectId
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request, $projectId)
    {
        $group = $this->groupContract->find($request->get('group_id'));

        if (! $this->checkPermissions('updateProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectContract->update($request->all(), $projectId);

        $project ? Flash::success(trans('messages.record_updated')) : Flash::error(trans('messages.record_updated_error'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }

    /**
     * Display project explore page.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        JavaScript::put([
            'projectId'    => $projectId,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('admin.grids.explore', [$projectId]),
            'exportUrl'    => route('admin.grids.project.export', [$projectId]),
            'showCheckbox' => true,
            'explore'      => true,
        ]);

        $subjectAssignedCount = $this->subjectContract->getSubjectAssignedCount($projectId);

        return view('frontend.projects.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId)
    {
        $project = $this->projectContract->getProjectForDelete($projectId);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            if ($project->nfnWorkflows->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                Flash::error(trans('messages.expedition_process_exists'));

                redirect()->route('admin.projects.index');
            }

            DeleteProject::dispatch($project);

            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('admin.projects.index');
        } catch (\Exception $e) {
            Flash::error(trans('messages.record_delete_error'));

            return redirect()->route('admin.projects.index');
        }
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @return mixed
     */
    public function ocr($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $this->ocrQueueService->processOcr($projectId) ?
            Flash::success(trans('messages.ocr_process_success')) :
            Flash::warning(trans('messages.ocr_process_error'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }
}
