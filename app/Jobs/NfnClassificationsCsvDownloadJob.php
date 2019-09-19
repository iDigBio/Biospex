<?php

namespace App\Jobs;

use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\PanoptesApiService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsCsvDownloadJob implements ShouldQueue
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
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param User $userContract
     * @return void
     */
    public function handle(
        PanoptesApiService $panoptesApiService,
        User $userContract
    )
    {
        if (count($this->sources) === 0)
        {
            return;
        }

        try
        {
            $responses = $panoptesApiService->panoptesClassificationsDownload($this->sources);
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
