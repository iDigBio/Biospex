<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\NfnClassificationsTranscriptJob;
use App\Repositories\Contracts\AmChartContract;
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
     * TestAppCommand constructor.
     */
    public function __construct(
        ProjectContract $projectContract,
        AmChartContract $contract,
        PanoptesTranscriptionContract $transcription,
        PanoptesTranscriptionProcess $process,
        Report $report

    )
    {
        parent::__construct();
        $this->contract = $contract;
        $this->projectContract = $projectContract;
        $this->transcription = $transcription;
        $this->report = $report;
        $this->process = $process;
    }

    /**
     *
     */
    public function handle()
    {
        /*
        $result = $this->contract->findBy('project_id', 26);
        \Log::alert(print_r(json_decode($result->data, true), true));
        return;

        $ids = [26];
        $job = new AmChartJob($ids);
        $job->handle($this->projectContract, $this->contract, $this->transcription);
        */
        $ids = explode(',', $this->argument('ids'));
        $job = new NfnClassificationsTranscriptJob($ids);
        $job->handle($this->process, $this->report);
    }
}