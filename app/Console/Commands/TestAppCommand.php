<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\WeDigBioDashboardJob;
use App\Repositories\Contracts\AmChartContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Services\Model\WeDigBioDashboardService;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use MongoCollection;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    public $ids;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var ProjectContract
     */
    private $projectContract;
    /**
     * @var AmChartContract
     */
    private $chart;
    /**
     * @var PanoptesTranscriptionContract
     */
    private $transcription;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(
        ProjectContract $projectContract,
        AmChartContract $chart,
        PanoptesTranscriptionContract $transcription
    )
    {
        parent::__construct();
        $this->projectContract = $projectContract;
        $this->chart = $chart;
        $this->transcription = $transcription;
    }

    /**
     *
     */
    public function handle()
    {
        $ids = [];
        $job = new AmChartJob($ids);
        $job->handle($this->projectContract, $this->chart, $this->transcription);
    }
}