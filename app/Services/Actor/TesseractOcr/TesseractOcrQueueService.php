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

namespace App\Services\Actor\TesseractOcr;

use App\Jobs\TesseractOcrProcessJob;
use App\Models\OcrQueue;
use Aws\Lambda\LambdaClient;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing OCR queue processing using Tesseract OCR via AWS Lambda.
 * Handles queue processing, status management, and Lambda availability checks.
 */
class TesseractOcrQueueService
{
    /**
     * Create a new TesseractOcrQueueService instance.
     *
     * @param  OcrQueue  $ocrQueue  The OCR queue model instance
     * @param  LambdaClient  $lambdaClient  AWS Lambda client instance
     */
    public function __construct(
        protected OcrQueue $ocrQueue,
        protected LambdaClient $lambdaClient
    ) {}

    /**
     * Process the next queue item in the OCR queue.
     *
     * @param  bool  $reset  Whether to reset and process from the first queue item
     *
     * @throws \Exception
     */
    public function processNextQueue(bool $reset = false): void
    {
        if ($this->ocrQueue->where('queued', 1)->where('error', 0)->exists()) {
            \Log::info('Queue already running');

            return; // Already running one
        }

        // Find the ID of the next candidate
        $nextQueue = $this->getNextQueue($reset);
        if (! $nextQueue) {
            \Log::info('No queue items available');

            return;
        }

        // ATOMIC LOCK: Try to update queued=1 WHERE id=X AND queued=0
        // This returns 1 if successful, 0 if someone else grabbed it first.
        $affected = $this->ocrQueue
            ->where('id', $nextQueue->id)
            ->where('queued', 0) // Critical check
            ->update(['queued' => 1, 'error' => 0]);

        if ($affected === 0) {
            // Someone else claimed it in the last millisecond. Abort.
            return;
        }

        // Reload the model to ensure we have a fresh state if needed, though ID is enough
        $queue = $this->ocrQueue->find($nextQueue->id);

        if (! $this->isLambdaReady()) {
            // Rollback the claim if lambda fails
            $queue->queued = 0;
            $queue->save();
            throw new \Exception("TesseractOcr Lambda concurrency is 0 — skipping queue #{$queue->id}");
        }

        \Log::info("TesseractOcr processing queue #{$queue->id}");
        TesseractOcrProcessJob::dispatch($queue);
    }

    /**
     * Retrieve the next queue item to be processed.
     *
     * @param  bool  $reset  Whether to reset and get the first queue item
     * @return OcrQueue|null The next queue item or null if none available
     */
    private function getNextQueue(bool $reset): ?OcrQueue
    {
        if ($reset) {
            return $this->ocrQueue->orderBy('id')->first();
        }

        return $this->ocrQueue
            ->where('queued', 0)
            ->where('error', 0)
            ->where('files_ready', 1)
            ->orderBy('id')
            ->first();
    }

    /**
     * Check if AWS Lambda function is ready for processing.
     *
     * @return bool True if Lambda function is available, false otherwise
     */
    private function isLambdaReady(): bool
    {
        try {
            $result = $this->lambdaClient->getFunctionConcurrency([
                'FunctionName' => 'BiospexTesseractOcr',
            ]);

            return ($result['ReservedConcurrentExecutions'] ?? 1) > 0;
        } catch (\Exception $e) {
            if (! str_contains($e->getMessage(), 'ResourceNotFoundException')) {
                Log::warning('Could not check OCR Lambda concurrency: '.$e->getMessage());
            }

            return true; // No limit = unlimited = safe
        }
    }
}
