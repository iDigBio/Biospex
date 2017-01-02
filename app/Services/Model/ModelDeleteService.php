<?php

namespace App\Services\Model;

ini_set('memory_limit', '1024M');

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
     * @var ExpeditionService
     */
    public $expeditionService;

    /**
     * @var SubjectService
     */
    private $subjectService;

    /**
     * @var NfnWorkflowService
     */
    public $nfnWorkflowService;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * ModelDeleteService constructor.
     * @param UserService $userService
     * @param GroupService $groupService
     * @param ProjectService $projectService
     * @param ExpeditionService $expeditionService
     * @param SubjectService $subjectService
     * @param NfnWorkflowService $nfnWorkflowService
     * @param Handler $handler
     */
    public function __construct(
        UserService $userService,
        GroupService $groupService,
        ProjectService $projectService,
        ExpeditionService $expeditionService,
        SubjectService $subjectService,
        NfnWorkflowService $nfnWorkflowService,
        Handler $handler
    )
    {
        $this->userService = $userService;
        $this->groupService = $groupService;
        $this->projectService = $projectService;
        $this->expeditionService = $expeditionService;
        $this->subjectService = $subjectService;
        $this->nfnWorkflowService = $nfnWorkflowService;
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

            $this->userService->repository->delete($record->id);

            return true;
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
                if ( ! $this->nfnWorkflowService->checkNfnWorkflowsEmpty($project->nfnWorkflows))
                {
                    session_flash_push('error', trans('expeditions.expedition_process_exists'));

                    return false;
                }
            }

            $this->groupService->repository->delete($id);

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

            if ( ! $this->nfnWorkflowService->checkNfnWorkflowsEmpty($record->nfnWorkflows))
            {
                session_flash_push('error', trans('expeditions.expedition_process_exists'));

                return false;
            }

            $this->projectService->repository->delete($id);

            return true;
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
            $record = $this->expeditionService->repository->skipCache()->with(['nfnWorkflow'])->find($id);

            if ( ! $this->nfnWorkflowService->checkNfnWorkflowsEmpty(collect($record->nfnWorkflow)))
            {
                session_flash_push('error', trans('expeditions.expedition_process_exists'));

                return false;
            }

            $subjects = $this->subjectService->repository->where(['expedition_ids' => (int) $id])->timeout(-1)->get();

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

            $this->expeditionService->repository->delete($id);

            return true;
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);

            return false;
        }
    }
}