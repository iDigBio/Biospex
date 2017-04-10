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
                echo 'Building workflow uri: ' . $expedition->nfnWorkflow->workflow . PHP_EOL;
                $uri = $api->buildClassificationCsvUri($expedition->nfnWorkflow->workflow);
                $request = $api->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                yield $request;
            }
        };

        try
        {
            $expeditions = $expeditionContract->setCacheLifetime(0)
                ->getExpeditionsForNfnClassificationProcess($this->ids);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {

                    echo 'Bad response: ' .  $response->getMessage() . PHP_EOL;
                    continue;
                    //$report->addError($response->getMessage());
                }

                echo 'Good response: ' . PHP_EOL;
            }

            /*
            if ($report->checkErrors())
            {
                $report->reportError();
            }
            */
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
        }
    }
}
