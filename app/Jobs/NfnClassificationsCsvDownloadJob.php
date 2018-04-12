<?php

namespace App\Jobs;

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

class NfnClassificationsCsvDownloadJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

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
        $this->onQueue(config('config.beanstalkd.classification'));
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

            \Log::info("Initital Download Resources: " . print_r($this->sources, true));
            $responses = $api->poolBatchRequest($requests());
            $expeditionIds = [];
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = 'Expedition Id: ' . $index . '<br />' . $response->getMessage();
                    continue;
                }

                $expeditionIds[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            NfnClassificationsReconciliationJob::dispatch($expeditionIds)->delay(1800);
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
