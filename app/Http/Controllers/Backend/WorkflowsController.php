<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowFormRequest;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Contracts\WorkflowContract;

class WorkflowsController extends Controller
{

    /**
     * @var WorkflowContract
     */
    private $workflowContract;
    
    /**
     * @var UserContract
     */
    private $userContract;
    
    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * WorkflowsController constructor.
     *
     * @param WorkflowContract $workflowContract
     * @param UserContract $userContract
     * @param ActorContract $actorContract
     */
    public function __construct(
        WorkflowContract $workflowContract,
        UserContract $userContract,
        ActorContract $actorContract
    )
    {
        $this->workflowContract = $workflowContract;
        $this->userContract = $userContract;
        $this->actorContract = $actorContract;
    }

    /**
     * Workflow index.
     *
     * @return mixed
     */
    public function index()
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $workflows = $this->workflowContract->findAll();
        $trashed = $this->workflowContract->onlyTrashed();
        $actors = $this->actorContract->findAll();

        return view('backend.workflows.index', compact('user', 'workflows', 'trashed', 'actors'));
    }

    /**
     * Edit workflow.
     *
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $workflows = $this->workflowContract->findAll();
        $trashed = $this->workflowContract->onlyTrashed();
        $actors = $this->actorContract->findAll();
        $workflow = $this->workflowContract->with('actors')->find($id);

        return view('backend.workflows.index', compact('user', 'workflows', 'trashed', 'actors', 'workflow'));
    }

    /**
     * Update workflow.
     *
     * @param WorkflowFormRequest $request
     * @param $id
     * @return mixed
     */
    public function update(WorkflowFormRequest $request, $id)
    {
        $workflow = $this->workflowContract->update($id, $request->all());
        $actors = [];
        foreach ($request->input('actors') as $key => $actor)
        {
            $actors[$actor['id']] = ['order' => $key];
        }

        $workflow->actors()->sync($actors);

        $workflow ? Toastr::success('Workflow has been updated.', 'Workflow Update')
            : Toastr::error('Workflow could not be updated.', 'Workflow Update');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Redirect to index.
     *
     * @return mixed
     */
    public function create()
    {
        return redirect()->route('admin.workflows.index');
    }

    /**
     * Create Workflow.
     *
     * @param WorkflowFormRequest $request
     * @return mixed
     */
    public function store(WorkflowFormRequest $request)
    {
        $workflow = $this->workflowContract->create($request->all());
        $actors = [];
        foreach ($request->input('actors') as $key => $actor)
        {
            $actors[$actor['id']] = ['order' => $key];
        }

        $workflow->actors()->sync($actors);

        $workflow ? Toastr::success('Workflow has been created.', 'Workflow Create')
            : Toastr::error('Workflow could not be created.', 'Workflow Create');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Soft delete workflow.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->workflowContract->update($id, ['enabled' => 0]);
        $result = $this->workflowContract->delete($id);

        $result ? Toastr::success('Workflow has been deleted.', 'Workflow Delete')
            : Toastr::error('Workflow could not be deleted.', 'Workflow Delete');


        return redirect()->route('admin.workflows.index');
    }

    /**
     * Force delete soft deleted records.
     * 
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $result = $this->workflowContract->forceDelete($id);

        $result ? Toastr::success('Workflow has been forcefully deleted.', 'Workflow Delete')
            : Toastr::error('Workflow could not be forcefully deleted.', 'Workflow Delete');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Enable Actor.
     *
     * @param $id
     * @return mixed
     */
    public function enable($id)
    {
        $result = $this->workflowContract->update($id, ['enabled' => 1]);

        $result ? Toastr::success('Workflow has been enabled.', 'Workflow Enable')
            : Toastr::error('Workflow could not be enabled.', 'Workflow Enable');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Disable Workflow.
     *
     * @param $id
     * @return mixed
     */
    public function disable($id)
    {
        $result = $this->workflowContract->update($id, ['enabled' => 0]);

        $result ? Toastr::success('Workflow has been disabled.', 'Workflow Disable')
            : Toastr::error('Workflow could not be disabled.', 'Workflow Disable');

        return redirect()->route('admin.workflows.index');
    }
}
