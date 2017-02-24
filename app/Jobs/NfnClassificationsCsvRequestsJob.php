<?php

namespace App\Jobs;

use App\Exceptions\NfnApiException;
use App\Jobs\Job;
use App\Repositories\Contracts\Expedition;
use App\Services\Api\NfnApi;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsCsvRequestsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Expedition ids pass to the job.
     *
     * @var null
     */
    public $ids;

    /**
     * Create a new job instance.
     *
     * NfNClassificationsCsvJob constructor.
     * @param null $ids
     */
    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    /**
     * Handle the job.
     *
     * @param Expedition $repo
     * @param NfnApi $api
     * @throws NfnApiException
     */
    public function handle(Expedition $repo, NfnApi $api)
    {
        $expeditions = $this->ids === null ?
            $repo->skipCache()->with(['stat'])->whereHas('stat', ['classification_process' => 0])->has('nfnWorkflow')->get() :
            $repo->skipCache()->with(['stat'])->whereHas('stat', ['classification_process' => 0])->has('nfnWorkflow')->whereIn('id', $this->ids);

        foreach ($expeditions as $expedition)
        {
            $this->sendCsvRequest($api, $expedition);
            $expedition->stat->classification_process = 1;
            $expedition->stat->save();
        }
    }

    /**
     * Send request if variables present.
     *
     * @param NfnApi $api
     * @param $expedition
     */
    private function sendCsvRequest(NfnApi $api, $expedition)
    {
        if ($this->checkForRequiredVariables($expedition))
        {
            return;
        }

        try
        {
            $api->requestClassificationCsvExport($expedition->nfnWorkflow->workflow_id);
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
}
