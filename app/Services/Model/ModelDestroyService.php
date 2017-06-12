<?php

namespace App\Services\Model;

ini_set('memory_limit', '1024M');

use App\Exceptions\BiospexException;
use App\Exceptions\Handler;
use App\Repositories\Contracts\ExpeditionContract;

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
     * @var ExpeditionContract
     */
    public $expeditionContract;

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
     * @param ExpeditionContract $expeditionContract
     * @param DownloadService $downloadService
     * @param Handler $handler
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionContract $expeditionContract,
        DownloadService $downloadService,
        Handler $handler
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionContract = $expeditionContract;
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
            $record = $this->userService->userContract->setCacheLifetime(0)
                ->with('trashedGroups')
                ->onlyTrashed($id);

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
            $record = $this->groupService->groupContract->setCacheLifetime(0)
                ->with('trashedProjects')
                ->onlyTrashed($id);

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
            $record = $this->projectService->projectContract
                ->setCacheLifetime(0)
                ->with(['expeditions', 'trashedSubjects'])
                ->onlyTrashed($id);

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
        try
        {
            $record = $this->expeditionContract->setCacheLifetime(0)
                ->with('downloads')
                ->onlyTrashed($id);

            if (isset($record->downloads))
            {
                $this->downloadService->deleteFiles($record->downloads);
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
}