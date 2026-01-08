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

    public int $timeout = 1500;

    protected int $fileId;

    protected int $queueId;

    protected string $subjectId;

    protected string $status;

    protected ?string $text;

    protected ?string $error;

    /**
     * Create a new job instance.
     *
     * @param  array  $data  Array containing OCR processing results
     */
    public function __construct(array $data)
    {
        $this->fileId = (int) ($data['fileId'] ?? 0);
        $this->queueId = (int) ($data['queueId'] ?? 0);
        $this->subjectId = (string) ($data['subjectId'] ?? '');
        $this->status = (string) ($data['status'] ?? 'failed');
        $this->text = $data['text'] ?? null;
        $this->error = $data['error'] ?? null;

        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * Execute the job to update OCR results.
     *
     * @param  SubjectService  $subjectService  Service to update subject data
     */
    public function handle(SubjectService $subjectService): void
    {
        $file = OcrQueueFile::find($this->fileId);

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
     */
    public function failed(Throwable $throwable): void
    {
        $file = OcrQueueFile::find($this->fileId);
        if ($file && $queue = OcrQueue::find($file->queue_id)) {
            $queue->error = 1;
            $queue->save();
        }
    }
}
