<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowFormRequest;
use App\Repositories\Interfaces\Actor;
use App\Repositories\Interfaces\User;
use App\Repositories\Interfaces\Workflow;

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
     * @param $workflowId
     * @return mixed
     */
    public function edit($workflowId)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $workflows = $this->workflowContract->all();
        $trashed = $this->workflowContract->getOnlyTrashed();
        $actors = $this->actorContract->all();
        $workflow = $this->workflowContract->findWith($workflowId, ['actors']);

        return view('backend.workflows.index', compact('user', 'workflows', 'trashed', 'actors', 'workflow'));
    }

    /**
     * Update workflow.
     *
     * @param WorkflowFormRequest $request
     * @param $workflowId
     * @return mixed
     */
    public function update(WorkflowFormRequest $request, $workflowId)
    {
        $workflow = $this->workflowContract->update($request->all(), $workflowId);
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
     * @param $workflowId
     * @return mixed
     */
    public function delete($workflowId)
    {
        $this->workflowContract->update(['enabled' => 0], $workflowId);
        $this->workflowContract->delete($workflowId) ?
            Flash::success('Workflow has been deleted.') :
            Flash::error('Workflow could not be deleted.');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Force delete soft deleted records.
     * 
     * @param $workflowId
     * @return mixed
     */
    public function trash($workflowId)
    {
        $this->workflowContract->destroy($workflowId) ?
            Flash::success('Workflow has been forcefully deleted.') :
            Flash::error('Workflow could not be forcefully deleted.');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Enable Actor.
     *
     * @param $workflowId
     * @return mixed
     */
    public function enable($workflowId)
    {
        $this->workflowContract->update(['enabled' => 1], $workflowId) ?
            Flash::success('Workflow has been enabled.') :
            Flash::error('Workflow could not be enabled.');

        return redirect()->route('admin.workflows.index');
    }

    /**
     * Disable Workflow.
     *
     * @param $workflowId
     * @return mixed
     */
    public function disable($workflowId)
    {
        $this->workflowContract->update(['enabled' => 0], $workflowId) ?
            Flash::success('Workflow has been disabled.') :
            Flash::error('Workflow could not be disabled.');

        return redirect()->route('admin.workflows.index');
    }
}
