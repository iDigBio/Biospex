<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\NfnPanoptes\ZooniverseExportReport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportReportJob
 *
 * @package App\Jobs
 */
class ZooniverseExportReportJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Actor $actor
     */
    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\NfnPanoptes\ZooniverseExportReport $zooniverseExportReport
     * @throws \Exception
     */
    public function handle(ZooniverseExportReport $zooniverseExportReport)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseExportReport->process($this->actor);
    }
}
