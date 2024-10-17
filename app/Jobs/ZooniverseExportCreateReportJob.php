<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\ExportQueue;
use App\Services\Actor\ActorDirectory;
use App\Services\Actor\Traits\ZooniverseErrorNotification;
use App\Services\Actor\Zooniverse\ZooniverseExportCreateReport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseExportCreateReportJob
 */
class ZooniverseExportCreateReportJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    public int $timeout = 900;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct(protected ExportQueue $exportQueue, protected ActorDirectory $actorDirectory)
    {
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(ZooniverseExportCreateReport $zooniverseExportReport)
    {
        $this->exportQueue->increment('stage');
        \Artisan::call('export:poll');
        $zooniverseExportReport->process($this->exportQueue, $this->actorDirectory);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $this->sendErrorNotification($this->exportQueue, $throwable);
    }
}
