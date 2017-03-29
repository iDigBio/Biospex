<?php

namespace App\Console\Commands;


use App\Exceptions\Handler;
use App\Jobs\NfnClassificationsCsvFileJob;
use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Api\NfnApi;
use App\Services\Google\Bucket;
use App\Services\Google\Drive;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Services\Google\Table;
use PulkitJalan\Google\Facades\Google;

class TestAppCommand extends Command
{

    use DispatchesJobs;
    public $projectContract;


    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var NfnClassificationsFusionTableJob
     */
    private $job;
    /**
     * @var TranscriptionLocationContract
     */
    private $locationContract;
    /**
     * @var Table
     */
    private $table;
    /**
     * @var Bucket
     */
    private $bucket;
    /**
     * @var Drive
     */
    private $drive;
    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;
    /**
     * @var NfnApi
     */
    private $api;
    /**
     * @var Report
     */
    private $report;
    /**
     * @var Handler
     */
    private $handler;


    /**
     * TestAppCommand constructor.
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        NfnApi $api,
        Report $report,
        Handler $handler
    )
    {
        parent::__construct();

        $this->expeditionContract = $expeditionContract;
        $this->api = $api;
        $this->report = $report;
        $this->handler = $handler;
    }

    public function handle()
    {
        $expeditions = $this->expeditionContract->setCacheLifetime(0)
            ->getExpeditionsForNfnClassificationProcess();
        foreach ($expeditions as $expedition)
        {
            echo $expedition->id . PHP_EOL;
        }
        //$job = new NfnClassificationsCsvFileJob(["17"]);
        //$job->handle($this->expeditionContract, $this->api, $this->report, $this->handler);
    }

}
