<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use App\Exceptions\NfnApiException;
use App\Repositories\Contracts\Expedition;
use App\Services\Actor\ActorService;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PulkitJalan\Google\Facades\Google;

class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var ActorService
     */
    private $service;
    /**
     * @var NfnApi
     */
    private $api;
    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * TestAppCommand constructor.
     * @param Expedition $expedition
     * @param NfnApi $api
     */
    public function __construct(Expedition $expedition, NfnApi $api)
    {
        parent::__construct();

        $this->api = $api;
        $this->expedition = $expedition;
    }

    /**
     * Execute the Job.
     */
    public function fire()
    {
            $ids = null;

            $expeditions = $this->ids === null ?
                $this->expedition->skipCache()->with(['stat'])->whereHas('stat', ['classification_process' => 0])->has('nfnWorkflow')->get() :
                $this->expedition->skipCache()->with(['stat'])->whereHas('stat', ['classification_process' => 0])->has('nfnWorkflow')->whereIn('id', $this->ids);

            foreach ($expeditions as $expedition)
            {
                $this->sendCsvRequest($expedition);
                $expedition->stat->classification_process = 1;
                $expedition->stat->save();
            }

            dd(count($expeditions));

            $workflow = 2046;

            $this->reconcileClassifications($workflow);

            return;

            //$this->api->setProvider();

            //$results = $this->sendCsvRequest($workflow);

            //$results = $this->checkCsvRequst($workflow);

            //if (isset($results['media'][0]['metadata']['state']) && $results['media'][0]['metadata']['state'] === 'ready')
            //{
            //    $this->api->retrieveClassificationCsvExport($results['media'][0]['src'], $workflow);
            //}
    }

    /**
     * Send request if variables present.
     *
     * @param NfnApi $api
     * @param $expedition
     */
    private function sendCsvRequest($expedition)
    {
        if ($this->checkForRequiredVariables($expedition))
        {
            return;
        }

        try
        {
            $this->api->requestClassificationCsvExport($expedition->nfnWorkflow->workflow_id);
        }
        catch (NfnApiException $e)
        {
            // Custom handler sends email reporting exception
        }
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    private function checkForRequiredVariables($expedition)
    {
        return null === $expedition
            || ! isset($expedition->nfnWorkflow)
            || null === $expedition->nfnWorkflow->workflow
            || null === $expedition->nfnWorkflow->project;
    }


    public function checkCsvRequst($workflow)
    {
        return $this->api->checkClassificationCsvExport($workflow);
    }

    public function reconcileClassifications()
    {
        $csvPath = storage_path('classifications/2079.csv');
        $recPath = storage_path('classifications/reconcile.csv');
        $sumPath = storage_path('classifications/summary.html');
        $sPath = storage_path('classifications/herbarium_wedigflplants-rose-gentians-of-florida-beauty-from-the-center-of-its-diversity-classifications.csv');
        //$command = "sudo python3 label_reconciliations/reconcile.py -r reconcile.csv -u raw_transcripts.csv -s summary.html $sPath";
        $command = "sudo python3 label_reconciliations/reconcile.py -r $recPath -u $csvPath -s $sumPath $sPath";
        $output = exec($command);
        dd($output);
    }

    public function googleTables()
    {
        // returns instance of \Google_Service_Storage
        $fusionTables = Google::make('fusiontables');
        $fusionTables->setScope('fusiontables');

        // list tables example
        dd($fusionTables->table->listTable());
    }
}
