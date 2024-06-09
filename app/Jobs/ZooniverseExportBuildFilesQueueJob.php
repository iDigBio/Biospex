<?php

namespace App\Jobs;

use App\Models\ExportQueue;
use App\Services\Actor\Zooniverse\Traits\ZooniverseErrorNotification;
use App\Services\Actor\Zooniverse\ZooniverseBuildQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ZooniverseExportBuildFilesQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * @var int
     */
    public int $timeout = 1800;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ZooniverseBuildQueue $zooniverseBuildQueue)
    {
        $zooniverseBuildQueue->buildFiles($this->exportQueue);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $throwable
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $this->sendErrorNotification($this->exportQueue, $throwable);
    }
}
