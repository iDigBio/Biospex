<?php

namespace App\Jobs;

use App\Interfaces\User;
use App\Notifications\JobError;
use Config;
use App\Services\Api\NfnApi;
use App\Interfaces\Expedition;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsCsvFileJob extends Job implements ShouldQueue
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
    private $sources = [];

    /**
     * @var array
     */
    private $reQueued = [];

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
     */
    public function handle(
        Expedition $expeditionContract,
        NfnApi $api,
        User $userContract
    )
    {
        try
        {
            $requests = function ($expeditions) use ($api)
            {
                foreach ($expeditions as $expedition)
                {
                    if ($api->checkForRequiredVariables($expedition))
                    {
                        continue;
                    }

                    $uri = $api->buildClassificationCsvUri($expedition->nfnWorkflow->workflow);
                    $request = $api->buildAuthorizedRequest('GET', $uri);

                    yield $expedition->id => $request;
                }
            };

            $expeditions = $expeditionContract->getExpeditionsForNfnClassificationProcess($this->ids);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = $response->getMessage();
                    continue;
                }

                $results = json_decode($response->getBody()->getContents());

                $this->checkState($results, $index);
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            $this->dispatchSources();
            $this->dispatchReQueued();
        }
        catch (\Exception $e)
        {
            return;
        }

    }

    /**
     * @param $results
     * @param $index
     */
    public function checkState($results, $index)
    {
        if ($results->media[0]->metadata->state === 'creating')
        {
            $this->reQueued[] = $index;
        }

        if ($results->media[0]->metadata->state === 'ready')
        {
            $this->sources[$index] = $results->media[0]->src;
        }
    }

    /**
     * Dispatch downloads
     */
    public function dispatchSources()
    {
        if (empty($this->sources))
        {
            return;
        }

        $this->dispatch((new NfnClassificationsCsvDownloadJob($this->sources))
            ->onQueue(Config::get('config.beanstalkd.classification')));
    }

    /**
     * Dispatch CSV file job if not ready
     */
    public function dispatchReQueued()
    {
        if (empty($this->reQueued))
        {
            return;
        }

        $this->dispatch((new NfnClassificationsCsvFileJob($this->reQueued))
            ->onQueue(Config::get('config.beanstalkd.classification'))
            ->delay(3600));
    }
}
