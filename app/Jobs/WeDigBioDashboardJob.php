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
    private $ids;

    /**
     * WeDigBioDashboardJob constructor.
     *
     * @param $ids array
     */
    public function __construct($ids)
    {
        $this->ids = $ids;
        $this->onQueue(config('config.beanstalkd.classification'));
    }

    /**
     * Handle job.
     *
     * @param WeDigBioDashboardService $weDigBioDashboardService
     */
    public function handle(
        WeDigBioDashboardService $weDigBioDashboardService
    )
    {

        if (empty($this->ids))
        {
            $this->delete();

            return;
        }

        try
        {
            collect($this->ids)->each(function($id) use ($weDigBioDashboardService){
                $expedition = $weDigBioDashboardService->getExpedition($id);

                $timestamp = DateHelper::mongoDbNowSubDateInterval('P2D');

                $transcriptions = $weDigBioDashboardService->getTranscriptions($expedition->id, $timestamp);

                $transcriptions->reject(function($transcription) use ($weDigBioDashboardService) {
                    return $weDigBioDashboardService->checkIfExists($transcription->_id);
                })->each(function ($transcription) use ($weDigBioDashboardService, $expedition) {
                    $weDigBioDashboardService->processTranscripts($transcription, $expedition);
                });
            });
        }
        catch (\Exception $e)
        {
            $this->delete();
            exit;
        }
    }
}
