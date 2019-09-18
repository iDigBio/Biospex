<?php

namespace App\Jobs;

use App\Models\NfnWorkflow as Model;
use App\Repositories\Interfaces\NfnWorkflow;
use App\Services\Api\NfnApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateNfnWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\NfnWorkflow
     */
    private $model;

    /**
     * UpdateNfnWorkflowJob constructor.
     *
     * @param \App\Models\NfnWorkflow $model
     */
    public function __construct(Model $model)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->model = $model;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Api\NfnApiService $nfnApiService
     * @param \App\Repositories\Interfaces\NfnWorkflow $nfnWorkflow
     */
    public function handle(NfnApiService $nfnApiService, NfnWorkflow $nfnWorkflow)
    {
        try {
            $workflow = $nfnApiService->getNfnWorkflow($this->model->panoptes_workflow_id);

            $projectId = $workflow['links']['project'];
            $subject_sets = isset($workflow['links']['subject_sets']) ? $workflow['links']['subject_sets'] : '';

            $project = $nfnApiService->getNfnProject($projectId);

            $values = [
                'panoptes_project_id'      => $projectId,
                'subject_sets' => $subject_sets,
                'slug'         => $project['slug'],
            ];

            $nfnWorkflow->update($values, $this->model->id);

        } catch (\Exception $e) {
            $this->delete();
        }

        $this->delete();
    }
}
