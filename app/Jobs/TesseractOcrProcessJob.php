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
use Aws\Sqs\SqsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to process OCR queue files using Tesseract OCR engine.
 * Sends unprocessed files to AWS SQS queue for OCR processing.
 */
class TesseractOcrProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  OcrQueue  $ocrQueue  The OCR queue to process
     */
    public function __construct(protected OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue->withoutRelations();
        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * Execute the job.
     * Retrieves unprocessed files from OCR queue and sends them to AWS SQS for processing.
     *
     * @param  SqsClient  $sqs  AWS SQS client instance
     */
    public function handle(SqsClient $sqs): void
    {
        // Grab ALL unprocessed files — no limit
        $files = $this->ocrQueue->files()->where('processed', 0)->get();

        // If no files → do nothing (could be re-run or race)
        if ($files->isEmpty()) {
            \Log::info("No unprocessed files in queue #{$this->ocrQueue->id} — skipping");
            $this->delete();

            return;
        }

        $queueUrl = $sqs->getQueueUrl([
            'QueueName' => config('services.aws.ocr_trigger'),
        ])['QueueUrl'];

        foreach ($files as $file) {
            $payload = [
                'ocrQueueFileId' => $file->id,
                'subjectId' => $file->subject_id,
                'access_uri' => $file->access_uri,
            ];

            $sqs->sendMessage([
                'QueueUrl' => $queueUrl,
                'MessageBody' => json_encode($payload),
            ]);
        }

        \Log::info("Sent {$files->count()} OCR tasks for queue #{$this->ocrQueue->id}");
        $this->delete();
    }

    /**
     * Handle a job failure.
     * Marks OCR queue as error and sends notification to admin.
     *
     * @param  Throwable  $throwable  Exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();

        $attributes = [
            'subject' => t('OCR Process Job Failed'),
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
