<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\ProjectFormRequest;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\User;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use App\Services\Model\NfnWorkflowService;
use App\Services\Model\ProjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{

    /**
     * @var User
     */
    public $user;

    /**
     * @var Project
     */
    public $project;

    /**
     * @var Request
     */
    public $request;

    /**
     * ProjectsController constructor.
     * @param User $user
     * @param Project $project
     * @param Request $request
     */
    public function __construct(
        User $user,
        Project $project,
        Request $request)
    {
        $this->user = $user;
        $this->project = $project;
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @param ProjectService $service
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ProjectService $service, NfnWorkflowService $nfnWorkflowService, $id = null)
    {
        $user = $this->user->with(['profile'])->find($this->request->user()->id);
        $projects = $this->project->all();
        $trashed = $this->project->trashed();

        $editProject = $id !== null ? $this->project->with(['nfnWorkflows'])->find($id) : null;

        $workflowEmpty = isset($editProject->nfnWorkflows) ?
            $nfnWorkflowService->checkNfnWorkflowsEmpty($editProject->nfnWorkflows) :
            true;
        $common = $service->setCommonVariables($this->request->user());
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
        $project = $this->project->create($request->all());

        if ($project)
        {
            Toastr::success('The Project has been created successfully.', 'Project Create');
            return redirect()->route('admin.projects.index');
        }

        Toastr::error('The Project could not be created.', 'Project Create');

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
        $project = $this->project->update($request->all(), $request->input('id'));

        $project ?
            Toastr::success('The Project has been updated.', 'Project Update') :
            Toastr::error('The Project failed to update.', 'Project Update');

        return redirect()->route('admin.projects.index');
    }

    /**
     * Delete project.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $service->deleteProject($id) ?
            Toastr::success('The Project has been deleted.', 'Project Delete') :
            Toastr::error('The Project could not be deleted.', 'Project Delete');

        return redirect()->route('admin.projects.index');
    }

    /**
     * Destroy project.
     *
     * @param ModelDestroyService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $service->destroyProject($id) ?
            Toastr::success('The Project has been forcefully deleted.', 'Project Destroy') :
            Toastr::error('The Project could not be forcefully deleted.', 'Project Destroy');

        return redirect()->route('admin.projects.index');
    }

    /**
     * Restore project.
     *
     * @param ModelRestoreService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $id)
    {
        $service->restoreProject($id) ?
            Toastr::success('The Project has been restored successfully.', 'Project Restore') :
            Toastr::error('Project could not be restored.', 'Project Restore');

        return redirect()->route('admin.projects.index');
    }
}
