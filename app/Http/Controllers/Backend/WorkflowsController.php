<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowFormRequest;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Workflow;
use Illuminate\Http\Request;

class WorkflowsController extends Controller
{

    /**
     * @var Workflow
     */
    private $workflow;
    
    /**
     * @var User
     */
    private $user;
    
    /**
     * @var Actor
     */
    private $actor;

    /**
     * WorkflowsController constructor.
     *
     * @param Workflow $workflow
     * @param User $user
     * @param Actor $actor
     */
    public function __construct(Workflow $workflow, User $user, Actor $actor)
    {
        $this->workflow = $workflow;
        $this->user = $user;
        $this->actor = $actor;
    }

    /**
     * Workflow index.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $workflows = $this->workflow->all();
        $actors = $this->actor->all();

        return view('backend.workflows.index', compact('user', 'workflows', 'actors'));
    }

    /**
     * Edit workflow.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $workflows = $this->workflow->all();
        $actors = $this->actor->all();
        $workflow = $this->workflow->with(['actors'])->find($id);

        return view('backend.workflows.index', compact('user', 'workflows', 'actors', 'workflow'));
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
        $workflow = $this->workflow->update($request->all(), $id);
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
        $workflow = $this->workflow->create($request->all());
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
        $result = $this->workflow->delete($id);

        $result ? Toastr::success('Workflow has been deleted.', 'Workflow Delete')
            : Toastr::error('Workflow could not be deleted.', 'Workflow Delete');


        return redirect()->route('admin.workflows.index');
    }


    public function forceDelete($id)
    {
        $workflow = $this->workflow->find($id);

        $result = $workflow->forceDelete();

        // Force deleting all related models...
        //$flight->history()->forceDelete();

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
        $result = $this->workflow->update(['enabled' => 1], $id);

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
        $result = $this->workflow->update(['enabled' => 0], $id);

        $result ? Toastr::success('Workflow has been disabled.', 'Workflow Disable')
            : Toastr::error('Workflow could not be disabled.', 'Workflow Disable');

        return redirect()->route('admin.workflows.index');
    }
}
