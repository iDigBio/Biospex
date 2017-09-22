<?php

namespace App\Services\Model;

use App\Repositories\Contracts\WorkflowManagerContract;

class WorkflowManagerService
{

    /**
     * @var WorkflowManagerContract
     */
    public $workflowManagerContract;

    public function __construct(WorkflowManagerContract $workflowManagerContract)
    {
        $this->workflowManagerContract = $workflowManagerContract;
    }

    /**
     * Toggle the workflow manager for an expeditions.
     *
     * @param $expeditionId
     */
    public function toggleExpeditionWorkflow($expeditionId)
    {
        $workflow = $this->workflowManagerContract->where('expedition_id', '=', $expeditionId)->findFirst();

        if ($workflow === null)
        {
            session_flash_push('error', trans('expeditions.process_no_exists'));

            return;
        }

        $workflow->stopped = 1;
        $this->workflowManagerContract->update($workflow->id, ['stopped' => 1]);
        session_flash_push('success', trans('expeditions.process_stopped'));

        return;
    }


}