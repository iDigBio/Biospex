<?php

namespace App\Services\Model;

use App\Exceptions\BiospexException;
use App\Exceptions\Handler;

class ModelDestroyService
{
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
     * @var Handler
     */
    public $handler;
    /**
     * @var DownloadService
     */
    private $downloadService;

    /**
     * ModelDeleteService constructor.
     * @param UserService $userService
     * @param GroupService $groupService
     * @param ProjectService $projectService
     * @param ExpeditionService $expeditionService
     * @param NfnWorkflowService $nfnWorkflowService
     * @param DownloadService $downloadService
     * @param Handler $handler
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionService $expeditionService,
        NfnWorkflowService $nfnWorkflowService,
        DownloadService $downloadService,
        Handler $handler
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionService = $expeditionService;
        $this->nfnWorkflowService = $nfnWorkflowService;
        $this->handler = $handler;
        $this->downloadService = $downloadService;
    }

    /**
     * Destroy user.
     *
     * @param $id
     * @return bool
     */
    public function destroyUser($id)
    {
        try
        {
            $record = $this->userService->repository->skipCache()->with(['trashedGroups'])->withTrashed($id);

            if ( ! $record->trashedGroups->isEmpty())
            {
                foreach ($record->trashedGroups as $group)
                {
                    $this->destroyGroup($group->id);
                }
            }

            $record->forceDelete();

            return true;
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Destory group.
     *
     * @param $id
     * @return bool
     */
    public function destroyGroup($id)
    {
        try
        {
            $record = $this->groupService->repository->skipCache()->with(['trashedProjects'])->withTrashed($id);

            if ( ! $record->trashedProjects->isEmpty())
            {
                foreach ($record->trashedProjects as $project)
                {
                    $this->destroyProject($project->id);
                }
            }

            $record->forceDelete();

            return true;
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Destroy project.
     *
     * @param $id
     * @return bool
     */
    public function destroyProject($id)
    {
        try
        {
            $record = $this->projectService->repository
                ->skipCache()
                ->with(['expeditions', 'trashedSubjects'])
                ->withTrashed($id);

            if ( ! $record->expeditions->isEmpty())
            {
                foreach ($record->expeditions as $expedition)
                {
                    $this->downloadService->deleteFiles($expedition->downloads);
                }
            }

            if ( ! $record->trashedSubjects->isEmpty())
            {
                $record->trashedSubjects()->timeout(-1)->forceDelete();
            }

            $record->forceDelete();

            return true;
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Destory expedition.
     *
     * @param $id
     * @return mixed
     */
    public function destroyExpedition($id)
    {
        $expedition = $this->expeditionService->repository->skipCache()->with(['downloads'])->withTrashed($id);

        if (isset($expedition->downloads))
        {
            $this->downloadService->deleteFiles($expedition->downloads);
        }

        return $this->expeditionService->repository->forceDelete($id);
    }
}