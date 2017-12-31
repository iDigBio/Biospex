<?php

namespace App\Jobs;

use App\Models\NfnWorkflow;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateNfnWorkflowJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var NfnWorkflow
     */
    private $nfnWorkflow;

    /**
     * UpdateNfnWorkflowJob constructor.
     *
     * @param NfnWorkflow $nfnWorkflow
     */
    public function __construct(NfnWorkflow $nfnWorkflow)
    {
        $this->nfnWorkflow = $nfnWorkflow;
    }

    /**
     * Execute job.
     *
     * @param NfnApi $nfnApi
     * @throws GuzzleException
     */
    public function handle(NfnApi $nfnApi)
    {
        if (null !== $this->nfnWorkflow->project)
        {
            return;
        }

        try {
            $this->retrieveWorkflow($nfnApi);
        }
        catch (\Exception $e)
        {
            $this->delete();
        }

        $this->delete();
    }

    /**
     * Retrieve workflow from api.
     *
     * @param NfnApi $nfnApi
     * @throws GuzzleException
     */
    private function retrieveWorkflow(NfnApi $nfnApi)
    {
        $nfnApi->setProvider();
        $nfnApi->checkAccessToken('nfnToken');
        $uri = $nfnApi->getWorkflowUri($this->nfnWorkflow->workflow);
        $request = $nfnApi->buildAuthorizedRequest('GET', $uri);
        $result = $nfnApi->sendAuthorizedRequest($request);

        $workflow = $result['workflows'][0];

        $this->nfnWorkflow->project = $workflow['links']['project'];
        $this->nfnWorkflow->subject_sets = isset($workflow['links']['subject_sets']) ? $workflow['links']['subject_sets'] : '';

        $this->nfnWorkflow->save();
    }
}
