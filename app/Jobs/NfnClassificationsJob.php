<?php

namespace App\Jobs;

use App\Exceptions\NfnApiException;
use App\Repositories\Contracts\Expedition;
use App\Services\Api\NfnApi;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class NfnClassificationsJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * @var bool
     */
    private $all;

    /**
     * NfnClassificationsJob constructor.
     *
     * @param $expeditionId
     * @param bool $all
     */
    public function __construct($expeditionId, $all = false)
    {
        $this->expeditionId = $expeditionId;
        $this->all = $all;
    }

    /**
     * Execute job.
     *
     * @param Expedition $expeditionRepo
     * @param NfnApi $api
     * @throws NfnApiException
     */
    public function handle(Expedition $expeditionRepo, NfnApi $api)
    {
        $expedition = $expeditionRepo->skipCache()
            ->with(['project.amChart', 'nfnWorkflow', 'nfnClassificationsLastId'])
            ->find($this->expeditionId);

        if ($this->checkForRequiredInformation($expedition))
        {
            $this->job->delete();

            return;
        }

        $classifications = $this->retrieveClassifications($api, $expedition);

        $this->processClassifications($expedition, $classifications);

        $this->dispatch((new ExpeditionStatJob($expedition->id))->onQueue(Config::get('config.beanstalkd.job')));

        if ( $expedition->project->amChart === null || ! $expedition->project->amChart->queued )
        {
            $this->dispatch((new AmChartJob($expedition->project->id))->onQueue(Config::get('config.beanstalkd.job')));
        }

        $this->job->delete();

    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkForRequiredInformation($expedition)
    {
        return null === $expedition
            || ! isset($expedition->nfnWorkflow)
            || null === $expedition->nfnWorkflow->workflow
            || null === $expedition->nfnWorkflow->project;
    }

    /**
     * Retrieve classifications from api.
     *
     * @param NfnApi $api
     * @param $expedition
     * @return mixed
     * @throws NfnApiException
     */
    private function retrieveClassifications(NfnApi $api, $expedition)
    {
        $api->setProvider();

        $values = [
            'project_id'  => $expedition->nfnWorkflow->project,
            'workflow_id' => $expedition->nfnWorkflow->workflow,
            'last_id'     => $this->all ? 0 : $expedition->nfnClassificationsLastId,
            'page_size'   => Config::get('config.expedition_size') * 3
        ];

        return $api->getClassifications($values);
    }

    /**
     * Process returned classifications.
     *
     * @param $expedition
     * @param array $classifications
     */
    private function processClassifications($expedition, array $classifications)
    {
        foreach ($classifications as $classification)
        {
            $attributes = [
                'nfn_workflow_id' => $expedition->nfnWorkflow->id,
                'classification_id' => $classification['id']
            ];

            $values = [
                'classification_id' => $classification['id'],
                'subjects'          => $classification['links']['subjects'],
                'started_at'        => $classification['metadata']['started_at'],
                'finished_at'       => $classification['metadata']['finished_at']
            ];

            $expedition->nfnWorkflow->classifications()->updateOrCreate($attributes, $values);
        }
    }
}
