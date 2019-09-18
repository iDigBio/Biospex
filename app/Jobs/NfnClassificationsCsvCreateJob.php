<?php

namespace App\Jobs;

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\NfnApiService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsCsvCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * Expedition expeditionIds pass to the job.
     *
     * @var null
     */
    private $expeditionIds;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * Create a new job instance.
     *
     * NfNClassificationsCsvJob constructor.
     * @param array $expeditionIds
     */
    public function __construct(array $expeditionIds = [])
    {
        $this->expeditionIds = $expeditionIds;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle the job.
     *
     * @param Expedition $expeditionContract
     * @param \App\Services\Api\NfnApiService $nfnApiService
     * @param User $userContract
     */
    public function handle(
        Expedition $expeditionContract,
        NfnApiService $nfnApiService,
        User $userContract
    )
    {
        try
        {
            $expeditions = $expeditionContract->getExpeditionsForNfnClassificationProcess($this->expeditionIds);

            $expeditionIds = [];
            $responses = $nfnApiService->nfnClassificationCreate($expeditions);
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = $response->getMessage();
                    continue;
                }

                $expeditionIds[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            empty($expeditionIds) ? $this->delete() :
                NfnClassificationsCsvFileJob::dispatch($expeditionIds)->delay(14400);
        }
        catch (\Exception $e)
        {
            return;
        }
    }
}
