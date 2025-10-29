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
use App\Models\User;
use App\Notifications\Generic;
use Aws\Sqs\SqsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ZooniverseExportProcessImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    public function handle(SqsClient $sqs): void
    {
        $this->exportQueue->load('expedition');

        $files = ExportQueueFile::where('queue_id', $this->exportQueue->id)
            ->where('processed', 0)
            ->orderBy('id')
            ->get();

        if ($files->isEmpty()) {
            throw new \Exception("No unprocessed files found for export queue ID: {$this->exportQueue->id}");
        }

        $queueUrl = $this->getQueueUrl($sqs, 'queue_image_tasks');
        $updatesQueueUrl = $this->getQueueUrl($sqs, 'queue_updates');
        $scratchDir = config('scratch_dir');
        $processDir = "{$scratchDir}/{$this->exportQueue->id}-".config('zooniverse.actor_id')."-{$this->exportQueue->expedition->uuid}";

        foreach ($files as $file) {
            $payload = [
                'processDir' => $processDir,
                'accessURI' => $file->access_uri,
                'subjectId' => $file->subject_id,
                's3Bucket' => config('filesystems.disks.s3.bucket'),
                'updatesQueueUrl' => $updatesQueueUrl,
                'queueId' => $this->exportQueue->id,
            ];

            $sqs->sendMessage([
                'QueueUrl' => $queueUrl,
                'MessageBody' => json_encode($payload),
            ]);
        }
    }

    private function getQueueUrl(SqsClient $sqs, string $key): string
    {
        $queueName = config("services.aws.{$key}");

        return $sqs->getQueueUrl(['QueueName' => $queueName])['QueueUrl'];
    }

    public function failed(Throwable $throwable): void
    {
        $this->exportQueue->error = 1;
        $this->exportQueue->save();

        $attributes = [
            'subject' => t('Expedition Export Process Error'),
            'html' => [
                t('Queue Id: %s', $this->exportQueue->id),
                t('Expedition Id: %s', $this->exportQueue->expedition_id ?? 'unknown'),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user?->notify(new Generic($attributes));
    }
}
