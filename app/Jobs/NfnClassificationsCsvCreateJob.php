<?php

namespace App\Jobs;

use App\Exceptions\Handler;
use App\Exceptions\HttpRequestException;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
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

                $uri = $api->buildClassificationCsvUri($expedition->nfnWorkflow->workflow);
                $request = $api->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                yield $expedition->id => $request;
            }
        };

        try
        {
            $expeditions = $expeditionContract->setCacheLifetime(0)
                ->getExpeditionsForNfnClassificationProcess($this->ids);

            $api->setProvider();
            $api->checkAccessToken('nfnToken');

            $ids = [];
            $responses = $api->poolBatchRequest($requests($expeditions));
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $report->addError($response->getMessage());
                    continue;
                }

                $ids[] = $index;
            }

            if ($report->checkErrors())
            {
                $report->reportError();
            }

            empty($ids) ? $this->delete() :
                $this->dispatch((new NfnClassificationsCsvFileJob($ids))
                    ->onQueue(config('config.beanstalkd.classification'))
                    ->delay(14400));
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
        }
    }
}
