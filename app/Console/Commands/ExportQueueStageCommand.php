<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Jobs\ZooniverseExportBuildCsvJob;
use App\Jobs\ZooniverseExportBuildZipJob;
use App\Jobs\ZooniverseExportCreateReportJob;
use App\Jobs\ZooniverseExportDeleteFilesJob;
use App\Jobs\ZooniverseExportProcessImagesJob;
use App\Services\Actor\ActorDirectory;
use App\Services\Actor\Zooniverse\ZooniverseExportQueue;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class ExportQueueCommand
 */
class ExportQueueStageCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:stage {queueId} {stage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue in mid process by giving stage.';

    /**
     * ExportQueueCommand constructor.
     */
    public function __construct(
        protected ZooniverseExportQueue $zooniverseExportQueue,
        protected ActorDirectory $actorDirectory)
    {
        parent::__construct();
    }

    /**
     * Handle job.
     */
    public function handle(): void
    {
        $queueId = (int) $this->argument('queueId');
        $stage = (int) $this->argument('stage');

        $exportQueue = $this->zooniverseExportQueue->getExportQueueForStageCommand($queueId);
        $exportQueue->stage = $stage;
        $exportQueue->error = 0;
        $exportQueue->save();

        $this->actorDirectory->setFolder($exportQueue->expedition_id, config('zooniverse.actor_id'), $exportQueue->expedition->uuid);
        $this->actorDirectory->setDirectories();

        match (true) {
            $stage === 1 => ZooniverseExportProcessImagesJob::dispatch($exportQueue, $this->actorDirectory),
            $stage === 2 => ZooniverseExportBuildCsvJob::dispatch($exportQueue, $this->actorDirectory),
            $stage === 3 => ZooniverseExportBuildZipJob::dispatch($exportQueue, $this->actorDirectory),
            $stage === 4 => ZooniverseExportCreateReportJob::dispatch($exportQueue, $this->actorDirectory),
            $stage === 5 => ZooniverseExportDeleteFilesJob::dispatch($exportQueue, $this->actorDirectory),
            default => $this->error('Invalid stage. Please select another.')
        };
    }
}
