<?php

namespace App\Services\Model;

use App\Repositories\Contracts\ExpeditionContract;

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
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * ModelDeleteService constructor.
     * @param UserService $userService
     * @param GroupService $groupService
     * @param ProjectService $projectService
     * @param ExpeditionContract $expeditionContract
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionContract $expeditionContract
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Restore user.
     *
     * @param $id
     * @return mixed
     */
    public function restoreUser($id)
    {
        return $this->userService->userContract->restore($id);
    }

    /**
     * Restore group.
     *
     * @param $id
     * @return mixed
     */
    public function restoreGroup($id)
    {
        return $this->groupService->groupContract->restore($id);
    }

    /**
     * Restore Project.
     *
     * @param $id
     * @return mixed
     */
    public function restoreProject($id)
    {
        return $this->projectService->projectContract->restore($id);
    }

    /**
     * Restore Expedition.
     * @param $id
     * @return mixed
     */
    public function restoreExpedition($id)
    {
        return $this->expeditionContract->restore($id);
    }
}