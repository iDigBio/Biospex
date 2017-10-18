<?php

namespace App\Jobs;

use App\Exceptions\BiospexException;
use App\Models\Traits\UuidTrait;
use App\Services\Model\WeDigBioDashboardService;
use App\Services\Report\Report;
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
    public $ids;

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
     * @param Report $report
     */
    public function handle(
        WeDigBioDashboardService $weDigBioDashboardService,
        Report $report
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

                $timestamp = $weDigBioDashboardService->getLatestTimestamp($expedition->uuid);
                if(empty($timestamp))
                {
                    return;
                }

                $date = mongo_date_interval($timestamp, 'P2D');

                $transcriptions = $weDigBioDashboardService->getTranscriptions($expedition->id, $date);

                $transcriptions->reject(function($transcription) use ($weDigBioDashboardService) {
                    return $weDigBioDashboardService->checkIfExists($transcription->_id);
                })->each(function ($transcription) use ($weDigBioDashboardService, $expedition) {
                    $weDigBioDashboardService->processTranscripts($transcription, $expedition);
                });
            });

            if ($report->checkErrors())
            {
                $report->reportError();
            }
        }
        catch (BiospexException $e)
        {
            $report->addError($e->getMessage());
        }
    }
}
