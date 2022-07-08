<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\NfnPanoptes\ZooniverseExportProcessImage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportProcessImageJob
 */
class ZooniverseExportProcessImageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var \App\Models\Actor
     */
    private Actor $actor;

    /**
     * @var int
     */
    public $timeout = 36000;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Actor $actor
     */
    public function __construct(Actor $actor)
    {
        //
        $this->actor = $actor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ZooniverseExportProcessImage $zooniverseProcessImage)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseProcessImage->process($this->actor);
    }
}
