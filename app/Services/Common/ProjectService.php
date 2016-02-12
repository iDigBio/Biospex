<?php

namespace Biospex\Services\Common;

use Biospex\Repositories\Contracts\Workflow;

class ProjectService
{
    /**
     * @var Workflow
     */
    private $workflow;
    /**
     * @var \Biospex\Services\Common\PermissionService
     */
    private $permission;

    /**
     * ProjectService constructor.
     * @param Workflow $workflow
     * @param PermissionService $permission
     */
    public function __construct(
        Workflow $workflow,
        PermissionService $permission
    ) {
        $this->workflow = $workflow;
        $this->permission = $permission;
    }

    /**
     * Check permissions.
     * @param $user
     * @param $classes
     * @param $ability
     * @return bool
     */
    public function checkPermissions($user, $classes, $ability)
    {
        return $this->permission->checkPermissions($user, $classes, $ability);
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
        $groups = $user->groups()->lists('label', 'id')->toArray();

        if (empty($groups)) {
            session_flash_push('success', trans('groups.group_required'));

            return redirect()->route('groups.create');
        }

        $workflows = ['--Select--'] + $this->workflow->selectList('workflow', 'id');
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;

        return compact('workflows', 'statusSelect', 'selectGroups');
    }
}

