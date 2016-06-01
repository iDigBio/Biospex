<?php

namespace App\Services\Common;

use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Workflow;

class ProjectService
{
    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * @var Group
     */
    private $group;

    /**
     * ProjectService constructor.
     * @param Workflow $workflow
     * @param Group $group
     */
    public function __construct(
        Workflow $workflow,
        Group $group
    ) {
        $this->workflow = $workflow;
        $this->group = $group;
    }

    /**
     * Check if a workflow exists
     * @param $expeditions
     * @return bool
     */
    public function checkWorkflow($expeditions)
    {
        foreach ($expeditions as $expedition) {
            if ( ! is_null($expedition->workflowManager))
            {
                return true;
            }

            return false;
        }
    }

    /**
     * Set common variables.
     *
     * @param $user
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function setCommonVariables($user)
    {
        $groups = $this->group->whereHas('users', ['user_id' => $user->id])->lists('label', 'id')->toArray();

        if (empty($groups)) {
            session_flash_push('success', trans('groups.group_required'));

            return redirect()->route('groups.create');
        }

        $workflows = ['--Select--'] + $this->workflow->orderBy(['workflow' => 'asc'])->lists('workflow', 'id')->toArray();
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;

        return compact('workflows', 'statusSelect', 'selectGroups');
    }
}

