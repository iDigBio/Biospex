<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\NfnPanoptes\ZooniverseExportDeleteFiles;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportDeleteFilesJob
 *
 * @package App\Jobs
 */
class ZooniverseExportDeleteFilesJob implements ShouldQueue, ShouldBeUnique
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
     * @param \App\Services\Actor\NfnPanoptes\ZooniverseExportDeleteFiles $zooniverseExportDeleteFiles
     * @throws \Exception
     */
    public function handle(ZooniverseExportDeleteFiles $zooniverseExportDeleteFiles)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseExportDeleteFiles->process($this->actor);
    }
}
