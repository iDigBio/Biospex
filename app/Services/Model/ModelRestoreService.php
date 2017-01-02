<?php

namespace App\Services\Model;

ini_set('memory_limit', '1024M');

class ModelRestoreService
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var GroupService
     */
    public $groupService;

    /**
     * @var ProjectService
     */
    public $projectService;

    /**
     * @var ExpeditionService
     */
    public $expeditionService;

    /**
     * @var NfnWorkflowService
     */
    public $nfnWorkflowService;

    /**
     * ModelDeleteService constructor.
     * @param UserService $userService
     * @param GroupService $groupService
     * @param ProjectService $projectService
     * @param ExpeditionService $expeditionService
     * @param NfnWorkflowService $nfnWorkflowService
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionService $expeditionService,
        NfnWorkflowService $nfnWorkflowService
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionService = $expeditionService;
        $this->nfnWorkflowService = $nfnWorkflowService;
    }

    /**
     * Restore user.
     *
     * @param $id
     * @return mixed
     */
    public function restoreUser($id)
    {
        return $this->userService->repository->skipCache()->withTrashed($id)->restore();
    }

    /**
     * Restore group.
     *
     * @param $id
     * @return mixed
     */
    public function restoreGroup($id)
    {
        return $this->groupService->repository->skipCache()->withTrashed($id)->restore();
    }

    /**
     * Restore Project.
     *
     * @param $id
     * @return mixed
     */
    public function restoreProject($id)
    {
        return $this->projectService->repository->skipCache()->withTrashed($id)->restore();
    }

    /**
     * Restore Expedition.
     *
     * @param $id
     */
    public function restoreExpedition($id)
    {
        return $this->expeditionService->repository->skipCache()->withTrashed($id)->restore();
    }
}