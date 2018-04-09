<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Expedition;
use App\Services\Api\NfnApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExpeditionStatJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * ExpeditionStatJob constructor.
     *
     * @param $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = (int) $expeditionId;
        $this->onQueue(config('config.beanstalkd.stat'));
    }

    /**
     * Execute job.
     *
     * @param \App\Repositories\Interfaces\Expedition $expedition
     * @param \App\Services\Api\NfnApi $api
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(Expedition $expedition, NfnApi $api)
    {
        $record = $expedition->findWith($this->expeditionId, ['stat', 'nfnActor']);
        $count = $expedition->getExpeditionSubjectCounts($this->expeditionId);

        $api->setProvider();
        $api->checkAccessToken('nfnToken');
        $uri = $api->getWorkflowUri($record->nfnWorkflow->workflow);
        $request = $api->buildAuthorizedRequest('GET', $uri);
        $result = $api->sendAuthorizedRequest($request);

        $workflow = $result['workflows'][0];
        $subject_count = $workflow['subjects_count'];
        $transcriptionCompleted = $workflow['classifications_count'];
        $transcriptionTotal = GeneralHelper::transcriptionsTotal($workflow['subjects_count']);
        $percentCompleted = GeneralHelper::transcriptionsPercentCompleted($transcriptionTotal, $transcriptionCompleted);

        $record->stat->local_subject_count = $count;
        $record->stat->subject_count = $subject_count;
        $record->stat->transcriptions_total = $transcriptionTotal;
        $record->stat->transcriptions_completed = $transcriptionCompleted;
        $record->stat->percent_completed = $percentCompleted;

        $record->stat->save();

        if ($workflow['finished_at'] !== null) {
            event('actor.pivot.completed', $record->nfnActor);
        }
    }
}
