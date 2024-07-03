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
use App\Services\Actor\Zooniverse\ZooniverseBuildZip;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseExportBuildZipJob
 */
class ZooniverseExportBuildZipJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * @var int
     */
    public int $timeout = 1800;

    /**
     * @var \App\Services\Actor\ActorDirectory
     */
    private ActorDirectory $actorDirectory;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \App\Services\Actor\ActorDirectory $actorDirectory
     */
    public function __construct(ExportQueue $exportQueue, ActorDirectory $actorDirectory)
    {
        $this->exportQueue = $exportQueue;
        $this->actorDirectory = $actorDirectory;
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\Zooniverse\ZooniverseBuildZip $zooniverseBuildZip
     * @throws \Exception
     */
    public function handle(ZooniverseBuildZip $zooniverseBuildZip): void
    {
        $this->exportQueue->increment('stage');
        \Artisan::call('export:poll');
        $zooniverseBuildZip->process($this->exportQueue, $this->actorDirectory);
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
