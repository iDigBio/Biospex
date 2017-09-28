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
use MongoDB\BSON\UTCDateTime;

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
        $transcriptions = $this->transcription->setCacheLifetime(0)
            ->where('classification_started_at', '>', strtotime('1970-01-20T00:00:00Z') * 1000)
            //->where('classification_finished_at', '>', new UTCDateTime(strtotime('1970-01-20T00:00:00Z') * 1000))
            ->get();
        dd(count($transcriptions));

        //dd(new UTCDateTime(strtotime('Thu, 28 Sep 2017 20:50:04 GMT') * 1000));
        //dd(new \DateTime(1506631804));
        /*
        $result = $this->contract->findBy('project_id', 26);
        \Log::alert(print_r(json_decode($result->data, true), true));
        return;

        $ids = [26];
        $job = new AmChartJob($ids);
        $job->handle($this->projectContract, $this->contract, $this->transcription);
        */

        //$job = new NfnClassificationsTranscriptJob($ids);
        //$job->handle($this->process, $this->report);
        //$transcript = $this->transcription->setCacheLifetime(0)->findBy('classification_id',19829916);
        //2016-10-28T19:55:07.000Z
        //$date = new UTCDateTime(strtotime('28-10-2016 19:55:00'));
        //$this->transcription->update($transcript->_id, ['classification_started_at' => 'Thu, 28 Sep 2017 20:50:04 GMT']);

    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $ids = [];
        $files = \File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $ids[] = basename($file, '.csv');
        }

        return $ids;
    }
}