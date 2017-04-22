<?php

namespace App\Jobs;

use App\Exceptions\NfnApiException;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class NfnClassificationsUpdateJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var array|null
     */
    private $ids;

    /**
     * NfnClassificationsUpdateJob constructor.
     * @param array$ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    /**
     * Execute job.
     *
     * @param ExpeditionContract $expeditionContract
     * @throws NfnApiException
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        $hasRelations = ['nfnWorkflow'];
        $withRelations = ['project.amChart', 'nfnWorkflow', 'nfnActor', 'stat'];

        $expeditions = empty($this->ids) ?
            $expeditionContract->setCacheLifetime(0)
                ->findAllHasRelationsWithRelations($hasRelations, $withRelations) :
            $expeditionContract->setCacheLifetime(0)
                ->findWhereInHasRelationsWithRelations(['id', [$this->ids]], $hasRelations, $withRelations);

        $projectIds = [];
        foreach ($expeditions as $expedition)
        {
            if ($this->checkIfExpeditionShouldProcess($expedition))
            {
                continue;
            }

            $this->updateExpeditionStats($expeditionContract, $expedition);
            $projectIds[] = $expedition->project->id;
        }

        $projectIds = array_values(array_unique($projectIds));

        $this->dispatch((new AmChartJob($projectIds))->onQueue(Config::get('config.beanstalkd.job')));

        $this->dispatch((new NfnClassificationsFusionTableJob($projectIds))->onQueue(config('config.beanstalkd.job')));

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
