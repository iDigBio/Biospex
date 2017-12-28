<?php

namespace App\Jobs;

use App\Exceptions\NfnApiException;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsUpdateJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var int expeditionId
     */
    private $expeditionId;

    /**
     * NfnClassificationsUpdateJob constructor.
     * @param int expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute Job.
     *
     * @param ExpeditionContract $expeditionContract
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        $withRelations = ['nfnWorkflow', 'nfnActor', 'stat'];

        $expedition = $expeditionContract->setCacheLifetime(0)
                ->has('nfnWorkflow')
                ->with($withRelations)
                ->find($this->expeditionId);

        if ($this->checkIfExpeditionShouldProcess($expedition))
        {
            $this->delete();

            return;
        }

        $this->updateExpeditionStats($expeditionContract, $expedition);

        $this->dispatch((new AmChartJob($expedition->project_id))
            ->onQueue(config('config.beanstalkd.chart')));

        $this->dispatch((new NfnClassificationsFusionTableJob($expedition->project_id))
            ->onQueue(config('config.beanstalkd.classification')));

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
     * @param ExpeditionContract $expeditionContract
     * @param $expedition
     */
    private function updateExpeditionStats(ExpeditionContract $expeditionContract, $expedition)
    {
        // Update stats
        $count = $expeditionContract->setCacheLifetime(0)->getExpeditionSubjectCounts($expedition->id);
        $expedition->stat->subject_count = $count;
        $expedition->stat->transcriptions_total = transcriptions_total($count);
        $expedition->stat->transcriptions_completed = transcriptions_completed($expedition->id);
        $expedition->stat->percent_completed = transcriptions_percent_completed($expedition->stat->transcriptions_total, $expedition->stat->transcriptions_completed);

        $expedition->stat->save();
    }
}
