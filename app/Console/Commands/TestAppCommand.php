<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\AmChartContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Services\Process\PanoptesTranscriptionProcess;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var AmChartContract
     */
    private $contract;
    /**
     * @var ProjectContract
     */
    private $projectContract;
    /**
     * @var PanoptesTranscriptionContract
     */
    private $transcription;
    /**
     * @var Report
     */
    private $report;
    /**
     * @var PanoptesTranscriptionProcess
     */
    private $process;
    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(
        ProjectContract $projectContract,
        AmChartContract $contract,
        PanoptesTranscriptionContract $transcription,
        PanoptesTranscriptionProcess $process,
        Report $report,
        ExpeditionContract $expeditionContract

    )
    {
        parent::__construct();
        $this->contract = $contract;
        $this->projectContract = $projectContract;
        $this->transcription = $transcription;
        $this->report = $report;
        $this->process = $process;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     *
     */
    public function handle()
    {
        $withRelations = ['project.amChart', 'nfnWorkflow', 'nfnActor', 'stat'];

        $expeditions = $this->expeditionContract->setCacheLifetime(0)
                ->has('nfnWorkflow')
                ->with($withRelations)
                ->findAll();

        $projectIds = [];
        foreach ($expeditions as $expedition)
        {
            if ($this->checkIfExpeditionShouldProcess($expedition))
            {
                continue;
            }

            $this->updateExpeditionStats($this->expeditionContract, $expedition);
            $projectIds[] = $expedition->project->id;
        }

        $projectIds = array_values(array_unique($projectIds));

        $job = new AmChartJob($projectIds);
        $job->handle($this->projectContract, $this->contract, $this->transcription);

        $this->dispatch((new NfnClassificationsFusionTableJob($projectIds))->onQueue(config('config.beanstalkd.classification')));
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