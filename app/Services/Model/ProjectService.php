<?php

namespace App\Services\Model;

use App\Jobs\BuildOcrBatchesJob;
use App\Repositories\Contracts\OcrQueueContract;
use App\Repositories\Contracts\ProjectContract;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ProjectService
{
    use DispatchesJobs;

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
     * @var OcrQueueContract
     */
    private $ocrQueueContract;

    /**
     * ProjectService constructor.
     *
     * @param ProjectContract $projectContract
     * @param WorkflowService $workflowService
     * @param GroupService $groupService
     * @param OcrQueueContract $ocrQueueContract
     */
    public function __construct(
        ProjectContract $projectContract,
        WorkflowService $workflowService,
        GroupService $groupService,
        OcrQueueContract $ocrQueueContract
    ) {
        $this->projectContract = $projectContract;
        $this->workflowService = $workflowService;
        $this->groupService = $groupService;
        $this->ocrQueueContract = $ocrQueueContract;
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

    /**
     * Check permissions for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function permissionCheck($projectId)
    {
        return $this->projectContract->with('group.permissions')->find($projectId);
    }

    /**
     * Process Ocr.
     *
     * @param $project
     * @param $expeditionId
     */
    public function processOcr($project, $expeditionId)
    {
        $queueCheck = $this->ocrQueueContract->setCacheLifetime(0)
            ->where('project_id', '=', $project->id)->findFirst();

        if ($queueCheck === null)
        {
            $this->dispatch((new BuildOcrBatchesJob($project->id, $expeditionId))->onQueue(config('config.beanstalkd.ocr')));

            session_flash_push('success', trans('expeditions.ocr_process_success'));
        }
        else
        {
            session_flash_push('warning', trans('expeditions.ocr_process_error'));
        }

        return;
    }

    /**
     * Get projects for ajax call to expeditions.
     *
     * @param $projectId
     * @return mixed
     */
    public function expeditionAjax($projectId)
    {
        return $this->projectContract->with(['expeditions.actors', 'expeditions.stat'])->find($projectId);
    }
}

