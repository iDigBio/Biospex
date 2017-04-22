<?php

namespace App\Jobs;

use App\Exceptions\Handler;
use App\Exceptions\HttpRequestException;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsCsvDownloadJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var array
     */
    public $sources;

    /**
     * NfnClassificationsCsvDownloadJob constructor.
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
    }

    /**
     * Execute the job.
     *
     * @param NfnApi $api
     * @param Report $report
     * @param Handler $handler
     * @return void
     */
    public function handle(
        NfnApi $api,
        Report $report,
        Handler $handler
    )
    {
        if (count($this->sources) === 0)
        {
            return;
        }

        $api->setProvider(false);

        $requests = function () use ($api)
        {
            foreach ($this->sources as $index => $source)
            {
                $filePath = config('config.classifications_download') . '/' . $index . '.csv';

                yield $index => function ($poolOpts) use ($api, $source, $filePath)
                {
                    $reqOpts = [
                        'sink' => $filePath
                    ];
                    if (is_array($poolOpts) && count($poolOpts) > 0)
                    {
                        $reqOpts = array_merge($poolOpts, $reqOpts); // req > pool
                    }

                    return $api->getHttpClient()->getAsync($source, $reqOpts);
                };
            }
        };

        try
        {
            $responses = $api->poolBatchRequest($requests());
            $ids = [];
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $report->addError('Expedition: ' . $index . '<br />' . $response->getMessage());
                    continue;
                }

                $ids[] = $index;
            }

            if ($report->checkErrors())
            {
                $report->reportError();
            }

            $this->dispatch((new NfnClassificationsReconciliationJob($ids))
                ->onQueue(config('config.beanstalkd.classification'))
                ->delay(1800));
        }
        catch (HttpRequestException $e)
        {
            $handler->report($e);
        }
    }
}
