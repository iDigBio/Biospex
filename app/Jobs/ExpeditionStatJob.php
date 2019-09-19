<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Expedition;
use App\Services\Api\NfnApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExpeditionStatJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * ExpeditionStatJob constructor.
     *
     * @param $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = (int) $expeditionId;
        $this->onQueue(config('config.stat_tube'));
    }

    /**
     * Execute job.
     *
     * @param \App\Repositories\Interfaces\Expedition $expedition
     * @param \App\Services\Api\NfnApiService $nfnApiService
     */
    public function handle(Expedition $expedition, NfnApiService $nfnApiService)
    {
        $record = $expedition->findWith($this->expeditionId, ['stat', 'nfnActor']);
        $count = $expedition->getExpeditionSubjectCounts($this->expeditionId);

        $workflow = $nfnApiService->getPanoptesWorkflow($record->panoptesProject->panoptes_workflow_id);

        $subject_count = $workflow['subjects_count'];
        $transcriptionCompleted = $workflow['classifications_count'];
        $transcriptionTotal = GeneralHelper::transcriptionsTotal($workflow['subjects_count']);
        $percentCompleted = GeneralHelper::transcriptionsPercentCompleted($transcriptionTotal, $transcriptionCompleted);

        $record->stat->local_subject_count = $count;
        $record->stat->subject_count = $subject_count;
        $record->stat->transcriptions_total = $transcriptionTotal;
        $record->stat->transcriptions_completed = $transcriptionCompleted;
        $record->stat->percent_completed = $percentCompleted;

        $record->stat->save();

        if ($workflow['finished_at'] !== null) {
            event('actor.pivot.completed', $record->nfnActor);
        }
    }
}
