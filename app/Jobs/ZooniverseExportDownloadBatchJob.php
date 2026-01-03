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

use App\Models\Download;
use App\Notifications\Generic;
use Aws\Sqs\SqsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ZooniverseExportDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public function __construct(protected Download $download)
    {
        $this->download = $this->download->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    public function handle(SqsClient $sqs): void
    {
        $this->download->load('expedition');

        $file = $this->download->file;
        $path = config('config.export_dir').'/'.$file;

        if (empty($file)) {
            throw new \InvalidArgumentException('Download file is missing');
        }

        $size = Storage::disk('s3')->size($path);

        $triggerQueueUrl = $this->getQueueUrl($sqs, 'batch_trigger');
        $updatesQueueUrl = $this->getQueueUrl($sqs, 'batch_update');

        $message = [
            'downloadId' => $this->download->id,
            'file' => $file,
            'exportPath' => $path,
            'totalSize' => $size,
            's3Bucket' => config('filesystems.disks.s3.bucket'),
            'updatesQueueUrl' => $updatesQueueUrl,
        ];

        $sqs->sendMessage([
            'QueueUrl' => $triggerQueueUrl,
            'MessageBody' => json_encode($message),
        ]);

        \Artisan::call('batch:listen-controller start');
    }

    private function getQueueUrl(SqsClient $sqs, string $key): string
    {
        $queueName = config("services.aws.queues.{$key}");

        return $sqs->getQueueUrl(['QueueName' => $queueName])['QueueUrl'];
    }

    public function failed(\Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Export Batch Failed to Queue'),
            'html' => [
                t('Failed to queue batch export for Expedition: %s', $this->download->expedition->title),
                t('File: %s', $this->download->file),
                t('Error: %s', $throwable->getMessage()),
            ],
        ];

        $this->download->expedition->project->group->owner->notify(new Generic($attributes, true));
    }
}
