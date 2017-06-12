<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\ProjectFormRequest;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\UserContract;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use App\Services\Model\ProjectService;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * ProjectsController constructor.
     * @param UserContract $userContract
     * @param ProjectContract $projectContract
     */
    public function __construct(
        UserContract $userContract,
        ProjectContract $projectContract
    )
    {
        $this->userContract = $userContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Display a listing of the resource.
     * @param ProjectService $service
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ProjectService $service, $id = null)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $projects = $this->projectContract->findAll();
        $trashed = $this->projectContract->onlyTrashed();

        $editProject = $id !== null ? $this->projectContract->with('nfnWorkflows')->find($id) : null;

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
        $project = $this->projectContract->create($request->all());

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
        $project = $this->projectContract->update($request->input('id'), $request->all());

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
