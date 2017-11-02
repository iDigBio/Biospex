<?php

namespace App\Jobs;

use Config;
use App\Exceptions\Handler;
use App\Exceptions\HttpRequestException;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
use App\Repositories\Contracts\ExpeditionContract;
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
    public $ids;

    /**
     * @var array
     */
    public $sources = [];

    /**
     * @var array
     */
    public $reQueued = [];

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
     * @param ExpeditionContract $expeditionContract
     * @param NfnApi $api
     * @param Report $report
     * @param Handler $handler
     */
    public function handle(
        ExpeditionContract $expeditionContract,
        NfnApi $api,
        Report $report,
        Handler $handler
    )
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

        try
        {
            $expeditions = $expeditionContract->setCacheLifetime(0)
                ->getExpeditionsForNfnClassificationProcess($this->ids);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $report->addError($response->getMessage());
                    continue;
                }

                $results = json_decode($response->getBody()->getContents());

                $this->checkState($results, $index);
            }

            if ($report->checkErrors())
            {
                $report->reportError();
            }

            $this->dispatchSources();
            $this->dispatchReQueued();
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
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
            \Log::info('creating ' . $index);
            $this->reQueued[] = $index;
        }

        if ($results->media[0]->metadata->state === 'ready')
        {
            \Log::info('ready ' . $index);
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

        \Log::info('dispatching ' . count($this->sources));
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

        \Log::info('requeued ' . count($this->sources));
        $this->dispatch((new NfnClassificationsCsvFileJob($this->reQueued))
            ->onQueue(Config::get('config.beanstalkd.classification'))
            ->delay(3600));
    }
}
