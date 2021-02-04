<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\ZooniverseRetrieveImage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportRetrieveImageJob
 *
 * @package App\Jobs
 */
class ZooniverseExportRetrieveImageJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

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
        $this->actor = $actor;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Actor\ZooniverseRetrieveImage $zooniverseRetrieveImage
     * @throws \Exception
     */
    public function handle(ZooniverseRetrieveImage $zooniverseRetrieveImage)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseRetrieveImage->process($this->actor);
    }
}
