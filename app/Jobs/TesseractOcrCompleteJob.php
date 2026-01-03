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

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrCompletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to handle completion of Tesseract OCR processing.
 * Processes completion logic, sends notifications, and cleans up queue records.
 *
 * @implements \Illuminate\Contracts\Queue\ShouldQueue
 */
class TesseractOcrCompleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\OcrQueue  $ocrQueue  The OCR queue record to process
     */
    public function __construct(protected OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue->withoutRelations();
        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    /**
     * Execute the job.
     *
     * @param  \App\Services\Actor\TesseractOcr\TesseractOcrCompletionService  $service  The completion service
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function handle(TesseractOcrCompletionService $service): void
    {
        // Run completion logic (report + notify)
        $service->complete($this->ocrQueue);

        // Delete the queue record
        $this->ocrQueue->delete();
    }

    /**
     * Handle a job failure.
     * Updates queue record with error status and notifies admin.
     *
     * @param  \Throwable  $throwable  The exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        // Mark queue as errored
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();

        $attributes = [
            'subject' => t('OCR Completion Job Failed'),
            'html' => [
                t('OCR Queue ID: %s', $this->ocrQueue->id),
                t('Project ID: %s', $this->ocrQueue->project_id),
                t('Expedition ID: %s', $this->ocrQueue->expedition_id ?? 'None'),
                t('Error: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
