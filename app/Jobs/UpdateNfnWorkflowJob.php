<?php

namespace App\Jobs;

use App\Models\NfnWorkflow;
use App\Services\Api\NfnApi;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateNfnWorkflowJob implements ShouldQueue
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
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute job.
     *
     * @param NfnApi $nfnApi
     *
     * TODO Once Expeditions with old workflows completed, change to only get Project via API
     *      But make sure a project will only have one workflow id.
     */
    public function handle(NfnApi $nfnApi)
    {
        /*
        if (null !== $this->nfnWorkflow->project)
        {
            return;
        }
        */

        try {

            $nfnApi->setProvider();
            $nfnApi->checkAccessToken('nfnToken');

            $uri = $nfnApi->getWorkflowUri($this->nfnWorkflow->workflow);
            $request = $nfnApi->buildAuthorizedRequest('GET', $uri);
            $workflowResult = $result = Cache::remember(md5($uri), 240, function () use ($nfnApi, $request){
                return $nfnApi->sendAuthorizedRequest($request);
            });

            $workflow = $workflowResult['workflows'][0];
            $project = $workflow['links']['project'];
            $subject_sets = isset($workflow['links']['subject_sets']) ? $workflow['links']['subject_sets'] : '';

            $uri = $nfnApi->getProjectUri($project);
            $request = $nfnApi->buildAuthorizedRequest('GET', $uri);
            $projectResult = $result = Cache::remember(md5($uri), 240, function () use ($nfnApi, $request){
                return $nfnApi->sendAuthorizedRequest($request);
            });

            $attributes = [
                'project_id' => $this->nfnWorkflow->project_id,
                'expedition_id' => $this->nfnWorkflow->expedition_id,
                'workflow' => $this->nfnWorkflow->workflow
            ];

            $values = [
                'project' => $project,
                'subject_sets' => $subject_sets,
                'slug' => $projectResult['projects'][0]['slug']
            ];

            NfnWorkflow::updateOrCreate($attributes, $values);

        }
        catch (\Exception $e)
        {
            $this->delete();
        }

        $this->delete();
    }
}
