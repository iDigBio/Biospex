<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 8/9/2016
 * Time: 1:10 PM
 */

namespace App\Jobs;


use App\Models\NfnWorkflow;
use App\Services\Api\NfnApi;
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
     */
    public function handle(NfnApi $nfnApi)
    {
        if (null !== $this->nfnWorkflow->project_id)
        {
            return;
        }

        $this->retrieveWorkflow($nfnApi);

        $this->job->delete();

    }

    /**
     * Retrieve workflow from api.
     *
     * @param NfnApi $nfnApi
     */
    private function retrieveWorkflow(NfnApi $nfnApi)
    {
        $nfnApi->setProvider();

        $result = json_decode($nfnApi->getWorkflow($this->nfnWorkflow->workflow), true);

        $workflow = $result['workflows'][0];

        $this->nfnWorkflow->project = $workflow['links']['project'];
        $this->nfnWorkflow->subject_sets = isset($workflow['links']['subject_sets']) ? $workflow['links']['subject_sets'] : '';

        $this->nfnWorkflow->save();
    }
}
