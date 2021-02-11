<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actor\ZooniverseConvertImage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ZooniverseExportConvertImageJob
 *
 * @package App\Jobs
 */
class ZooniverseExportConvertImageJob implements ShouldQueue, ShouldBeUnique
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
     * Execute the job.
     *
     * @param \App\Services\Actor\ZooniverseConvertImage $zooniverseConvertImage
     * @throws \Exception
     */
    public function handle(ZooniverseConvertImage $zooniverseConvertImage)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $zooniverseConvertImage->process($this->actor);
    }
}
