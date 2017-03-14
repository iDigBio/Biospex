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

        $expeditions = $expeditionContract->setCacheLifetime(0)
            ->getExpeditionsForNfnClassificationProcess($this->ids);

        if (count($expeditions) === 0)
        {
            return;
        }

        $api->setProvider();
        $api->checkAccessToken('nfnToken');

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
            $responses = $api->poolBatchRequest($requests($expeditions));
            $sources = [];
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $report->addError($response->getMessage());
                }
                else
                {
                    $results = json_decode($response->getBody()->getContents());
                    $sources[$index] = $results->media[0]->src;
                }
            }

            if ($report->checkErrors())
            {
                $report->reportError();
            }

            $this->dispatch((new NfnClassificationsCsvDownloadJob($sources))->onQueue(Config::get('config.beanstalkd.job')));
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
        }

    }
}
