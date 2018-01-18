<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsUpdateJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * NfnClassificationsUpdateJob constructor.
     * @param int $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.beanstalkd.classification'));
    }

    /**
     * Execute job.
     *
     * @param Expedition $expeditionContract
     */
    public function handle(Expedition $expeditionContract)
    {
        $expedition = $expeditionContract->getExpeditionsHavingNfnWorkflows($this->expeditionId);

        if ($this->checkIfExpeditionShouldProcess($expedition))
        {
            $this->delete();

            return;
        }

        $this->updateExpeditionStats($expeditionContract, $expedition);

        AmChartJob::dispatch($expedition->project_id);
        NfnClassificationsFusionTableJob::dispatch($expedition->project_id);

        $this->delete();
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkIfExpeditionShouldProcess($expedition)
    {
        return null === $expedition
            || ! isset($expedition->nfnWorkflow)
            || null === $expedition->nfnWorkflow->workflow
            || null === $expedition->nfnWorkflow->project
            || null === $expedition->nfnActor;
    }

    /**
     * Update expedition stats.
     *
     * @param Expedition $expeditionContract
     * @param $expedition
     */
    private function updateExpeditionStats(Expedition $expeditionContract, $expedition)
    {
        // Update stats
        $count = $expeditionContract->getExpeditionSubjectCounts($expedition->id);
        $expedition->stat->subject_count = $count;
        $expedition->stat->GeneralHelper::transcriptionsTotal = GeneralHelper::transcriptionsTotal($count);
        $expedition->stat->GeneralHelper::transcriptionsCompleted = GeneralHelper::transcriptionsCompleted($expedition->id);
        $expedition->stat->percent_completed = GeneralHelper::transcriptionsPercentCompleted($expedition->stat->transcriptions_total, $expedition->stat->transcriptions_completed);

        $expedition->stat->save();
    }
}
