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

namespace App\Services\Actor\Zooniverse;

use App\Jobs\ZooniverseExportProcessImagesJob;
use App\Models\ExportQueue;
use Aws\Lambda\LambdaClient;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing Zooniverse export queue processing and Lambda function interactions.
 */
class ZooniverseExportQueueService
{
    /**
     * Create a new ZooniverseExportQueueService instance.
     *
     * @param  ExportQueue  $exportQueue  The export queue model instance
     * @param  LambdaClient  $lambdaClient  The AWS Lambda client instance
     */
    public function __construct(
        protected ExportQueue $exportQueue,
        protected LambdaClient $lambdaClient
    ) {}

    /**
     * Process the next available export queue item if conditions are met.
     * Checks for existing queued items, Lambda availability, and dispatches processing job.
     *
     * @throws \Exception
     */
    public function processNextQueue(): void
    {
        if ($this->exportQueue->where('queued', 1)->where('error', 0)->exists()) {
            return;
        }

        // 1. Find the candidate ID (don't lock yet)
        $nextQueue = $this->exportQueue->with('expedition')
            ->where('error', 0)
            ->where('queued', 0)
            ->where('files_ready', 1)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $nextQueue) {
            return;
        }

        // 2. ATOMIC LOCK: Try to claim it. Returns 1 if successful, 0 if lost race.
        $affected = $this->exportQueue
            ->where('id', $nextQueue->id)
            ->where('queued', 0) // Ensure it's still unqueued
            ->update(['queued' => 1, 'stage' => 1]);

        if ($affected === 0) {
            return; // Someone else claimed it milliseconds ago
        }

        // 3. Reload fresh model
        $exportQueue = $this->exportQueue->find($nextQueue->id);

        // 4. Perform Checks (Lambda)
        if (! $this->isLambdaReady()) {
            // Rollback claim if check fails
            $exportQueue->queued = 0;
            $exportQueue->stage = 0; // Or appropriate previous stage
            $exportQueue->save();
            throw new \Exception("Export Lambda concurrency is 0 — skipping queue #{$exportQueue->id}");
        }

        // 5. Start Listener & Dispatch
        \Artisan::queue('update:listen-controller start')
            ->onQueue(config('config.queue.default'));

        $exportQueue->stage = 1;
        $exportQueue->save();

        ZooniverseExportProcessImagesJob::dispatch($exportQueue);
    }

    /**
     * Check if the Lambda function is available for processing.
     *
     * @return bool Returns true if Lambda function is ready for processing, false otherwise
     */
    private function isLambdaReady(): bool
    {
        try {
            $result = $this->lambdaClient->getFunctionConcurrency([
                'FunctionName' => 'BiospexImageProcess',
            ]);

            return ($result['ReservedConcurrentExecutions'] ?? 1) > 0;
        } catch (\Exception $e) {
            if (! str_contains($e->getMessage(), 'ResourceNotFoundException')) {
                Log::warning('Could not check Export Lambda concurrency: '.$e->getMessage());
            }

            return true; // No limit = safe to run
        }
    }
}
