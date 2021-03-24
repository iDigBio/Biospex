<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\ZooniverseBuildQueue;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportBuildQueueJob
 *
 * @package App\Jobs
 */
class ZooniverseExportBuildQueueJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * @var int
     */
    public $timeout = 3600;

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
     * @param \App\Services\Actor\ZooniverseBuildQueue $zooniverseBuildQueue
     * @throws \Exception
     */
    public function handle(ZooniverseBuildQueue $zooniverseBuildQueue)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseBuildQueue->process($this->actor);
    }
}
