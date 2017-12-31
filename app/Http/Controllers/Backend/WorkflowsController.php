<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowFormRequest;
use App\Interfaces\Actor;
use App\Interfaces\User;
use App\Interfaces\Workflow;

class WorkflowsController extends Controller
{

    /**
     * @var Workflow
     */
    private $workflowContract;
    
    /**
     * @var User
     */
    private $userContract;
    
    /**
     * @var Actor
     */
    private $actorContract;

    /**
     * WorkflowsController constructor.
     *
     * @param Workflow $workflowContract
     * @param User $userContract
     * @param Actor $actorContract
     */
    public function __construct(
        Workflow $workflowContract,
        User $userContract,
        Actor $actorContract
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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $workflows = $this->workflowContract->all();
        $trashed = $this->workflowContract->getOnlyTrashed();
        $actors = $this->actorContract->all();

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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $workflows = $this->workflowContract->all();
        $trashed = $this->workflowContract->getOnlyTrashed();
        $actors = $this->actorContract->all();
        $workflow = $this->workflowContract->findWith($id, ['actors']);

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
        $workflow = $this->workflowContract->update($request->all(), $id);
        $actors = collect($request->input('actors'))->mapWithKeys(function ($actor, $key){
            return [$actor['id'] => ['order' => $key]];
        })->toArray();

        $workflow->actors()->sync($actors);

        $workflow ? Flash::success('Workflow has been updated.')
            : Flash::error('Workflow could not be updated.');

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
        $actors = collect($request->input('actors'))->mapWithKeys(function ($actor, $key){
            return [$actor['id'] => ['order' => $key]];
        })->toArray();

        $workflow->actors()->sync($actors);

        $workflow ? Flash::success('Workflow has been created.')
            : Flash::error('Workflow could not be created.');

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
        $this->workflowContract->update(['enabled' => 0], $id);
        $this->workflowContract->delete($id) ?
            Flash::success('Workflow has been deleted.') :
            Flash::error('Workflow could not be deleted.');

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
        $this->workflowContract->destroy($id) ?
            Flash::success('Workflow has been forcefully deleted.') :
            Flash::error('Workflow could not be forcefully deleted.');

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
        $this->workflowContract->update(['enabled' => 1], $id) ?
            Flash::success('Workflow has been enabled.') :
            Flash::error('Workflow could not be enabled.');

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
        $this->workflowContract->update(['enabled' => 0], $id) ?
            Flash::success('Workflow has been disabled.') :
            Flash::error('Workflow could not be disabled.');

        return redirect()->route('admin.workflows.index');
    }
}
