<?php

namespace App\Services\Model;

ini_set('memory_limit', '1024M');

use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Support\Facades\Event;
use App\Exceptions\BiospexException;
use App\Exceptions\Handler;

class ModelDeleteService
{
    /**
     * @var UserService
     */
    public $userService;

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
     * @var SubjectService
     */
    private $subjectService;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * ModelDeleteService constructor.
     * @param UserService $userService
     * @param GroupService $groupService
     * @param ProjectService $projectService
     * @param ExpeditionContract $expeditionContract
     * @param SubjectService $subjectService
     * @param Handler $handler
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionContract $expeditionContract,
        SubjectService $subjectService,
        Handler $handler
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionContract = $expeditionContract;
        $this->subjectService = $subjectService;
        $this->handler = $handler;
    }

    /**
     * Delete user.
     *
     * @param $id
     * @return bool
     */
    public function deleteUser($id)
    {
        try
        {
            $record = $this->userService->repository->skipCache()->with(['ownGroups'])->find($id);

            foreach ($record->ownGroups as $group)
            {
                if ( ! $this->deleteGroup($group->id))
                {
                    return false;
                }
            }

            return $record->delete();
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Delete group.
     *
     * @param $id
     * @return bool
     */
    public function deleteGroup($id)
    {
        try
        {
            $record = $this->groupService->repository->skipCache()->with(['projects.nfnWorkflows'])->find($id);

            foreach ($record->projects as $project)
            {
                if ( ! $project->nfnWorkflows->isEmpty())
                {
                    session_flash_push('error', trans('expeditions.expedition_process_exists'));

                    return false;
                }
            }

            $record->delete();

            //$groups = $service->groupService->model->whereHas('users', ['user_id' => $user->id])->get();
            //Request::session()->put('groups', $groups);
            Event::fire('group.deleted');

            return true;
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Delete project.
     *
     * @param $id
     * @return bool
     */
    public function deleteProject($id)
    {
        try
        {
            $record = $this->projectService->repository->skipCache()->with(['nfnWorkflows'])->find($id);

            if ( ! $record->nfnWorkflows->isEmpty())
            {
                session_flash_push('error', trans('expeditions.expedition_process_exists'));

                return false;
            }

            return $record->delete();
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }

    /**
     * Delete expedition.
     *
     * @param $id
     * @return bool
     */
    public function deleteExpedition($id)
    {
        try
        {
            $record = $this->expeditionContract->setCacheLifetime(0)->with(['nfnWorkflow'])->find($id);

            if (isset($record->nfnWorkflow))
            {
                session_flash_push('error', trans('expeditions.expedition_process_exists'));

                return false;
            }

            $subjects = $this->subjectService->repository->where(['expedition_ids' => (int) $id])->get();

            if ( ! $subjects->isEmpty())
            {
                $this->subjectService->detach($subjects, $id);
            }

            $values = [
                'subject_count' => 0,
                'transcriptions_total' => 0,
                'transcriptions_completed' => 0,
                'percent_completed' => 0.00
            ];

            $record->stat()->updateOrCreate(['expedition_id' => $record->id], $values);

            return $record->delete();
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }
}