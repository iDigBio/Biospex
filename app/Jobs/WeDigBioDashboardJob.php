<?php

namespace App\Jobs;

use App\Facades\DateHelper;
use App\Models\Traits\UuidTrait;
use App\Services\Model\WeDigBioDashboardService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeDigBioDashboardJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs, UuidTrait;

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
