<?php

namespace App\Services\Common;

use App\Repositories\Contracts\Workflow;

class ProjectService
{
    /**
     * @var Workflow
     */
    private $workflow;
    /**
     * @var \App\Services\Common\PermissionService
     */
    private $permission;

    /**
     * Construct.
     *
     * @param Workflow $workflow
     * @param \App\Services\Common\PermissionService $permission
     * @internal param Request $request
     * @internal param Router $router
     * @internal param UrlGenerator $url
     * @internal param Repository $config
     * @internal param Group $group
     * @internal param Project $project
     * @internal param Auth $auth
     * @internal param User $user
     * @internal param Actor $actor
     * @internal param Sentry $sentry
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

