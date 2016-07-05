<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Workflow;
use Illuminate\Http\Request;

use App\Http\Requests;

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
     * WorkflowsController constructor.
     *
     * @param Workflow $workflow
     * @param User $user
     */
    public function __construct(Workflow $workflow, User $user)
    {
        $this->workflow = $workflow;
        $this->user = $user;
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
