<?php

namespace App\Jobs;

use App\Models\NfnWorkflow;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateNfnWorkflowJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->onQueue(config('config.beanstalkd.classification_tube'));
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
