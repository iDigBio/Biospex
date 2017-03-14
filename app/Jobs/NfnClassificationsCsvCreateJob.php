<?php

namespace App\Jobs;

use App\Exceptions\Handler;
use App\Exceptions\HttpRequestException;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsCsvCreateJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

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

                yield $request;
            }
        };

        try
        {
            $responses = $api->poolBatchRequest($requests);
            foreach ($responses as $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $report->addError($response->getMessage());
                }
            }

            if ($report->checkErrors())
            {
                $report->reportError();
            }
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
        }
    }
}
