<?php

namespace App\Jobs;

use App\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\NfnApi;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
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
    private $sources;

    /**
     * @var array
     */
    private $errorMessages = [];

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
     * @param User $userContract
     * @return void
     */
    public function handle(
        NfnApi $api,
        User $userContract
    )
    {
        if (count($this->sources) === 0)
        {
            return;
        }

        try
        {
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

            $responses = $api->poolBatchRequest($requests());
            $ids = [];
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = 'Expedition Id: ' . $index . '<br />' . $response->getMessage();
                    continue;
                }

                $ids[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            $this->dispatch((new NfnClassificationsReconciliationJob($ids))
                ->onQueue(config('config.beanstalkd.classification'))
                ->delay(1800));
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
