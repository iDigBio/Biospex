<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\NfnClassification;
use App\Services\Api\NfnApi;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class NfnClassificationsUpdate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classifications:update {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions.';
    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * @var NfnApi
     */
    private $api;
    /**
     * @var NfnClassification
     */
    private $classification;

    /**
     * Create a new command instance.
     *
     * @param Expedition $expedition
     * @param NfnClassification $classification
     * @param NfnApi $api
     */
    public function __construct(Expedition $expedition, NfnClassification $classification, NfnApi $api)
    {
        parent::__construct();
        $this->expedition = $expedition;
        $this->api = $api;
        $this->classification = $classification;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expeditions = $this->getExpeditions();

        $this->api->setProvider();

        foreach ($expeditions as $expedition)
        {
            $this->retrieveClassifications($expedition);

            $this->dispatch((new ExpeditionStatJob($expedition->project->id, $expedition->id))->onQueue(Config::get('config.beanstalkd.job')));
        }

        Artisan::call('amchart:update');
    }

    /**
     * Retrieve expeditions.
     *
     * @return array
     */
    private function getExpeditions()
    {
        $id = $this->argument('id');

        return null === $id ?
            $expeditions = $this->expedition->skipCache()->with(['project'])->whereNotNull('nfn_workflow_id')->get() :
            $expeditions = $this->expedition->skipCache()->with(['project'])->where(['id' => $id])->whereNotNull('nfn_workflow_id')->get();
    }

    /**
     * Retrieve classifications from api.
     *
     * @param $expedition
     */
    private function retrieveClassifications($expedition)
    {
        $pageSize = Config::get('config.expedition_size') * 3;
        $result = json_decode($this->api->getClassifications($expedition->nfn_workflow_id, $pageSize), true);

        foreach ($result['classifications'] as $classification)
        {
            $this->saveClassification($expedition, $classification);
        }
    }

    /**
     * @param $expedition
     * @param $result
     */
    private function saveClassification($expedition, $result)
    {
        $data = [
            'classification_id' => $result['id'],
            'project_id'        => $expedition->project->id,
            'expedition_id'     => $expedition->id,
            'started_at'        => $result['metadata']['started_at'],
            'finished_at'       => $result['metadata']['finished_at']
        ];

        $this->classification->updateOrCreate(['classification_id' => $result['id']], $data);
    }
}
