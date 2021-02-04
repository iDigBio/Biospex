<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\ZooniverseCheckImageCount;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportCheckImageCountJob
 *
 * @package App\Jobs
 */
class ZooniverseExportCheckImageCountJob implements ShouldQueue, ShouldBeUnique
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
        //
        $this->actor = $actor;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\ZooniverseCheckImageCount $zooniverseCheckImageCount
     * @throws \Exception
     */
    public function handle(ZooniverseCheckImageCount $zooniverseCheckImageCount)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseCheckImageCount->process($this->actor);
    }
}
