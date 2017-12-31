<?php

namespace App\Jobs;

use App\Interfaces\Expedition;
use App\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsCsvCreateJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * Expedition ids pass to the job.
     *
     * @var null
     */
    private $ids;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * Create a new job instance.
     *
     * NfNClassificationsCsvJob constructor.
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
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

            $expeditions = $expeditionContract->getExpeditionsForNfnClassificationProcess($this->ids);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $ids = [];
            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = $response->getMessage();
                    continue;
                }

                $ids[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            empty($ids) ? $this->delete() :
                $this->dispatch((new NfnClassificationsCsvFileJob($ids))
                    ->onQueue(config('config.beanstalkd.classification'))
                    ->delay(14400));
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
