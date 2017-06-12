<?php

namespace App\Services\Model;

use App\Repositories\Contracts\ProjectContract;

class ProjectService
{
    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * @var WorkflowService
     */
    private $workflowService;

    /**
     * @var GroupService
     */
    private $groupService;

    /**
     * ProjectService constructor.
     *
     * @param ProjectContract $projectContract
     * @param WorkflowService $workflowService
     * @param GroupService $groupService
     */
    public function __construct(
        ProjectContract $projectContract,
        WorkflowService $workflowService,
        GroupService $groupService
    ) {
        $this->projectContract = $projectContract;
        $this->workflowService = $workflowService;
        $this->groupService = $groupService;
    }

    /**
     * Set common variables.
     *
     * @param $user
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function setCommonVariables($user)
    {
        $groups = $this->groupService->getUsersGroupsSelect($user);

        if (empty($groups)) {
            session_flash_push('success', trans('groups.group_required'));

            return redirect()->route('groups.create');
        }

        $workflows = $this->workflowService->select();
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;

        return compact('workflows', 'statusSelect', 'selectGroups');
    }

}

