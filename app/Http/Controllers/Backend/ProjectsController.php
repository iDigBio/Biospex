<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\ProjectFormRequest;
use App\Interfaces\User;
use App\Services\Model\ProjectService;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{

    /**
     * @var ProjectService
     */
    private $projectService;

    /**
     * @var User
     */
    private $userContract;

    /**
     * ProjectsController constructor.
     * @param ProjectService $projectService
     * @param User $userContract
     */
    public function __construct(
        ProjectService $projectService,
        User $userContract
    )
    {
        $this->projectService = $projectService;
        $this->userContract = $userContract;
    }

    /**
     * Display a listing of the resource.
     * @param ProjectService $service
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ProjectService $service, $id = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $projects = $this->projectService->getallProjects();
        $trashed = $this->projectService->getTrashedProjects();

        $editProject = $id !== null ? $this->projectService->findWith($id, ['nfnWorkflows']) : null;

        $workflowEmpty = ! isset($editProject->nfnWorkflows) || $editProject->nfnWorkflows->isEmpty();
        $common = $service->setCommonVariables(request()->user());
        $vars = [
            'user' => $user,
            'projects' => $projects,
            'trashed' => $trashed,
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
        $this->projectService->createProject($request->all());

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
        $this->projectService->updateProject($request->all(), $request->input('id'));

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
        $project = $this->projectService->findWith($projectId, ['nfnWorkflows']);

        $this->projectService->deleteProject($project);

        return redirect()->route('admin.projects.index');
    }

    /**
     * Destroy project.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group', 'expeditions.downloads', 'subjects'], true);

        $this->projectService->destroyProject($project);

        return redirect()->route('admin.projects.index');
    }

    /**
     * Restore project.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($projectId)
    {
        $project = $this->projectService->findWith($projectId, [], true);

        $this->projectService->restoreProject($project);

        return redirect()->route('admin.projects.index');
    }
}
