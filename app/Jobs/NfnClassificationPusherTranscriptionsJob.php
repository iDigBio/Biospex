<?php

namespace App\Jobs;

use App\Facades\DateHelper;
use App\Models\Traits\UuidTrait;
use App\Services\Model\PusherTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationPusherTranscriptionsJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UuidTrait;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $expeditionIds;

    /**
     * NfnClassificationPusherTranscriptionsJob constructor.
     *
     * @param $expeditionIds array
     */
    public function __construct($expeditionIds)
    {
        $this->expeditionIds = $expeditionIds;
        $this->onQueue(config('config.beanstalkd.classification'));
    }

    /**
     * Handle job.
     *
     * @param PusherTranscriptionService $dashboardService
     */
    public function handle(
        PusherTranscriptionService $dashboardService
    )
    {

        if (empty($this->expeditionIds))
        {
            $this->delete();

            return;
        }

        try
        {
            collect($this->expeditionIds)->each(function($expeditionId) use ($dashboardService){
                $expedition = $dashboardService->getExpedition($expeditionId);

                $timestamp = DateHelper::mongoDbNowSubDateInterval('P2D');

                $transcriptions = $dashboardService->getTranscriptions($expedition->id, $timestamp);

                $transcriptions->reject(function($transcription) use ($dashboardService) {
                    return $dashboardService->checkClassification($transcription);
                })->each(function ($transcription) use ($dashboardService, $expedition) {
                    $dashboardService->processTranscripts($transcription, $expedition);
                });
            });
        }
        catch (\Exception $e)
        {
            $this->delete();
        }
    }
}
