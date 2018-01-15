<?php

namespace App\Jobs;

use App\Facades\DateHelper;
use App\Models\Traits\UuidTrait;
use App\Services\Model\WeDigBioDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WeDigBioDashboardJob extends Job implements ShouldQueue
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
     * WeDigBioDashboardJob constructor.
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
     * @param WeDigBioDashboardService $dashboardService
     */
    public function handle(
        WeDigBioDashboardService $dashboardService
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
                    return $dashboardService->checkIfExists($transcription->_id);
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
