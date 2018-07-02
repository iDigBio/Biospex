<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\ProjectFormRequest;
use App\Jobs\DeleteProject;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\User;
use App\Services\Model\CommonVariables;
use App\Services\Model\ProjectService;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{
    /**
     * @var User
     */
    private $userContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Model\CommonVariables
     */
    private $commonVariables;

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Services\Model\CommonVariables $commonVariables
     * @param User $userContract
     */
    public function __construct(
        Project $projectContract,
        Group $groupContract,
        CommonVariables $commonVariables,
        User $userContract
    )
    {
        $this->userContract = $userContract;
        $this->projectContract = $projectContract;
        $this->commonVariables = $commonVariables;
        $this->groupContract = $groupContract;
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($projectId = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $projects = $this->projectContract->all();

        $editProject = $projectId !== null ? $this->projectContract->findWith($projectId, ['nfnWorkflows']) : null;

        $workflowEmpty = ! isset($editProject->nfnWorkflows) || $editProject->nfnWorkflows->isEmpty();
        $groups = $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $common = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        $vars = [
            'user' => $user,
            'projects' => $projects,
            'editProject' => $editProject,
            'workflowEmpty' => $workflowEmpty
        ];

        $variables = array_merge($common, $vars);

        return view('backend.projects.index', $variables);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $this->projectContract->create($request->all());

        return redirect()->route('admin.projects.index')->withInput();
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request)
    {
        $this->projectContract->update($request->all(), $request->input('id'));

        return redirect()->route('admin.projects.index');
    }

    /**
     * Delete project.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId)
    {
        $project = $this->projectContract->getProjectForDelete($projectId);

        if ($project->nfnWorkflows->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
            Flash::error(trans('messages.expedition_process_exists'));

            redirect()->route('admin.projects.index');
        }

        DeleteProject::dispatch($project);

        return redirect()->route('admin.projects.index');
    }

}
