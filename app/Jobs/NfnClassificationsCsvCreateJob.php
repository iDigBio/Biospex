<?php

namespace App\Jobs;

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsCsvCreateJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * Expedition expeditionIds pass to the job.
     *
     * @var null
     */
    private $expeditionIds;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * Create a new job instance.
     *
     * NfNClassificationsCsvJob constructor.
     * @param array $expeditionIds
     */
    public function __construct(array $expeditionIds = [])
    {
        $this->expeditionIds = $expeditionIds;
        $this->onQueue(config('config.beanstalkd.classification_tube'));
    }

    /**
     * Handle the job.
     *
     * @param Expedition $expeditionContract
     * @param NfnApi $api
     * @param User $userContract
     */
    public function handle(
        Expedition $expeditionContract,
        NfnApi $api,
        User $userContract
    )
    {
        try
        {
            /**
             * @param array $expeditions
             * @return \Generator
             */
            $requests = function ($expeditions) use ($api)
            {
                foreach ($expeditions as $expedition)
                {
                    if ($api->checkForRequiredVariables($expedition))
                    {
                        continue;
                    }

                    $uri = $api->buildClassificationCsvUri($expedition->nfnWorkflow->workflow);
                    $request = $api->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                    yield $expedition->id => $request;
                }
            };

            $expeditions = $expeditionContract->getExpeditionsForNfnClassificationProcess($this->expeditionIds);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $expeditionIds = [];
            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = $response->getMessage();
                    continue;
                }

                $expeditionIds[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            empty($expeditionIds) ? $this->delete() :
                NfnClassificationsCsvFileJob::dispatch($expeditionIds)->delay(14400);
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
