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

use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Traits\NotifyOnJobFailure;
use Aws\Sqs\SqsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Process images for Zooniverse export by sending them to SQS queue for processing.
 */
class ZooniverseExportProcessImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotifyOnJobFailure, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param  ExportQueue  $exportQueue  The export queue model instance
     */
    public function __construct(protected ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job to process images for Zooniverse export.
     * Sends unprocessed files to SQS queue for processing.
     *
     * @param  SqsClient  $sqs  AWS SQS client instance
     *
     * @throws \Exception When no unprocessed files are found
     */
    public function handle(SqsClient $sqs): void
    {
        $this->exportQueue->load('expedition');

        if (! ExportQueueFile::where('queue_id', $this->exportQueue->id)
            ->where('processed', 0)
            ->exists()) {
            throw new \Exception("No unprocessed files found for export queue ID: {$this->exportQueue->id}");
        }

        $files = ExportQueueFile::where('queue_id', $this->exportQueue->id)
            ->where('processed', 0)
            ->orderBy('id')
            ->cursor();

        $queueUrl = $this->getQueueUrl($sqs, 'queue_image_tasks');
        $updatesQueueUrl = $this->getQueueUrl($sqs, 'queue_export_update');
        $processDir = "{$this->exportQueue->id}-".config('zooniverse.actor_id')."-{$this->exportQueue->expedition->uuid}";

        $processedCount = 0;
        foreach ($files as $file) {
            $payload = [
                'processDir' => $processDir,
                'accessURI' => $file->access_uri,
                'subjectId' => $file->subject_id,
                's3Bucket' => config('filesystems.disks.s3.bucket'),
                'updatesQueueUrl' => $updatesQueueUrl,
                'queueId' => $this->exportQueue->id,
            ];

            try {
                $sqs->sendMessage([
                    'QueueUrl' => $queueUrl,
                    'MessageBody' => json_encode($payload),
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to send for file {$file->id}: ".$e->getMessage());
            }
            $processedCount++;
        }
        \Log::info("Sent {$processedCount} records to SQS");
    }

    /**
     * Get AWS SQS queue URL for given queue key.
     *
     * @param  SqsClient  $sqs  AWS SQS client instance
     * @param  string  $key  Queue configuration key
     * @return string Queue URL
     */
    private function getQueueUrl(SqsClient $sqs, string $key): string
    {
        $queueName = config("services.aws.queues.{$key}");

        return $sqs->getQueueUrl(['QueueName' => $queueName])['QueueUrl'];
    }

    /**
     * Handle job failure by updating export queue and notifying admin.
     *
     * @param  Throwable  $throwable  The exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        $this->exportQueue->error = 1;
        $this->exportQueue->save();

        $this->exportQueue->error = 1;
        $this->exportQueue->save();

        $this->notifyGroupOnFailure($this->exportQueue, $throwable);
    }
}
