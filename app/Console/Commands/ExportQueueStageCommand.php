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
use App\Jobs\ZooniverseExportCreateReportJob;
use App\Jobs\ZooniverseExportDeleteFilesJob;
use App\Jobs\ZooniverseExportProcessImagesJob;
use App\Models\ExportQueue;
use App\Services\Actor\Zooniverse\ZooniverseZipTriggerService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ExportQueueStageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-stage {queueId} {--stage= : Stage number (1-5) to override queue stage (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Admin command to manually trigger export queue stages for failed exports. Uses current queue stage if --stage not provided.';

    protected ZooniverseZipTriggerService $zipTriggerService;

    public function __construct(
        ZooniverseZipTriggerService $zipTriggerService
    ) {
        parent::__construct();
        $this->zipTriggerService = $zipTriggerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queueId = $this->argument('queueId');
        $stageOption = $this->option('stage');
        $queue = ExportQueue::with('expedition')->find($queueId);

        if (! $queue) {
            $this->error('Queue not found');

            return CommandAlias::FAILURE;
        }

        // Validate stage option if provided
        if ($stageOption !== null) {
            $stageOption = (int) $stageOption;
            if ($stageOption < 1 || $stageOption > 5) {
                $this->error('Stage must be between 1 and 5');

                return CommandAlias::FAILURE;
            }
            $stage = $stageOption;
        } else {
            $stage = $queue->stage;
        }

        $queue->stage = $stage;
        $queue->queued = 1;
        $queue->error = 0;
        $queue->save();

        \Artisan::call('update:listen-controller start');

        try {
            match ($queue->stage) {
                1 => ZooniverseExportProcessImagesJob::dispatch($queue),
                2 => ZooniverseExportBuildCsvJob::dispatch($queue),
                3 => $this->sendBiospexZipTrigger($queue),
                4 => ZooniverseExportCreateReportJob::dispatch($queue),
                5 => ZooniverseExportDeleteFilesJob::dispatch($queue),
                default => throw new \InvalidArgumentException('Invalid stage'),
            };

            $this->info("Successfully processed stage {$queue->stage} for queue ID {$queue->id}");

            return CommandAlias::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error processing stage {$queue->stage}: ".$e->getMessage());

            return CommandAlias::FAILURE;
        }
    }

    /**
     * Send ZIP trigger to AWS SQS for stage 2 processing.
     *
     * @throws \Exception
     */
    protected function sendBiospexZipTrigger(ExportQueue $queue): void
    {
        $this->info('Sending ZIP trigger to AWS SQS...');

        // Process the complete zip trigger workflow
        $exportData = $this->zipTriggerService->processZipTrigger($queue);

        // Update queue stage to indicate zip creation is in progress
        $queue->stage = 3;
        $queue->save();

        $this->info("ZIP trigger sent successfully - {$exportData['fileCount']} files ({$exportData['totalSize']} bytes)");
    }
}
