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
use App\Models\OcrQueueFile;
use App\Services\Subject\SubjectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to update OCR processing results for a subject.
 *
 * This job handles updating the OCR text results for subjects after processing,
 * managing the OCR queue status, and tracking completion of OCR batches.
 *
 * @implements \Illuminate\Contracts\Queue\ShouldQueue
 */
class TesseractOcrUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    protected int $ocrQueueFileId;

    protected string $subjectId;

    protected string $status;

    protected ?string $text;

    protected ?string $error;

    /**
     * Create a new job instance.
     *
     * @param  array  $data  Array containing OCR processing results with keys:
     *                       - ocrQueueFileId: ID of the OCR queue file
     *                       - subjectId: ID of the subject
     *                       - status: Processing status ('success'|'failed')
     *                       - text: OCR extracted text (optional)
     *                       - error: Error message if failed (optional)
     */
    public function __construct(array $data)
    {
        $this->ocrQueueFileId = $data['ocrQueueFileId'] ?? 0;
        $this->subjectId = $data['subjectId'] ?? '';
        $this->status = $data['status'] ?? 'failed';
        $this->text = $data['text'] ?? null;
        $this->error = $data['error'] ?? null;

        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * Execute the job to update OCR results.
     *
     * Updates the subject's OCR text if processing was successful,
     * or stores error message if processing failed.
     * Marks the queue file as processed and checks for queue completion.
     *
     * @param  SubjectService  $subjectService  Service to update subject data
     */
    public function handle(SubjectService $subjectService): void
    {
        $file = OcrQueueFile::find($this->ocrQueueFileId);

        if (! $file || $file->subject_id !== $this->subjectId) {
            return; // stale or malformed
        }

        $wasProcessed = $file->processed;

        if ($this->status === 'success') {
            $text = trim(preg_replace('/\s+/', ' ', $this->text));
            $text = $text !== '' ? $text : '[OCR produced no text]';

            $subjectService->update(['ocr' => $text], $this->subjectId);
        } else {
            $errorText = '[OCR Failed] '.($this->error ?? 'Unknown error');
            $subjectService->update(['ocr' => $errorText], $this->subjectId);
        }

        $file->processed = 1;
        $file->save();

        // Only check completion if this file just flipped to processed
        if (! $wasProcessed) {
            $this->checkIfOcrComplete($file);
        }
    }

    /**
     * Check if all files in the OCR queue have been processed.
     *
     * If all files are processed, dispatches the completion job.
     *
     * @param  OcrQueueFile  $file  The processed queue file
     */
    private function checkIfOcrComplete(OcrQueueFile $file): void
    {
        $queue = OcrQueue::find($file->queue_id);

        if (! $queue || ! $queue->queued) {
            return;
        }

        $total = $queue->files()->count();
        $processed = $queue->files()->where('processed', 1)->count();

        if ($total === $processed) {
            TesseractOcrCompleteJob::dispatch($queue);
        }
    }

    /**
     * Handle a job failure.
     *
     * Marks the queue as errored and logs the failure.
     * Does not send email notifications to prevent spam.
     *
     * @param  Throwable  $throwable  The exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        // DO NOT SEND EMAIL HERE — would spam 20K times
        // Just mark the queue as failed so the cron skips it

        $queue = OcrQueue::find($this->data['ocrQueueFileId'] ?? null);
        if ($queue) {
            $queue->error = 1;
            $queue->save();
        }

        // Optional: log once per queue, not per file
        \Log::error('OCR Update Job failed (queue will be marked errored)', [
            'ocrQueueFileId' => $this->ocrQueueFileId ?? 'unknown',
            'subjectId' => $this->subjectId ?? 'unknown',
            'error' => $throwable->getMessage(),
        ]);
    }
}
