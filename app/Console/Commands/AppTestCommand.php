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

use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class AppCommand
 */
class AppTestCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    protected SqsClient $sqs;

    /**
     * Create a new command instance.
     */
    public function __construct(SqsClient $sqs)
    {

        parent::__construct();
        $this->sqs = $sqs;
    }

    public function handle(): void
    {
        // 471
        $download = \App\Models\Download::with('expedition')->find(1813);
        $file = $download->file;
        $path = config('config.export_dir').'/'.$file;

        if (empty($file)) {
            throw new \InvalidArgumentException('Download file is missing');
        }

        $size = Storage::disk('s3')->size($path);

        $triggerQueueUrl = $this->getQueueUrl($this->sqs, 'queue_batch_trigger');
        $updatesQueueUrl = $this->getQueueUrl($this->sqs, 'queue_batch_update');

        $this->info($size);
        $this->info($triggerQueueUrl);
        $this->info($updatesQueueUrl);

        $message = [
            'downloadId' => $download->id,
            'file' => $file,
            'path' => $path,
            'totalSize' => $size,
            's3Bucket' => config('filesystems.disks.s3.bucket'),
            'updatesQueueUrl' => $updatesQueueUrl,
        ];

        $this->info(json_encode($message));

    }

    private function getQueueUrl(SqsClient $sqs, string $configKey): string
    {
        $queueName = config("services.aws.{$configKey}");

        return $sqs->getQueueUrl(['QueueName' => $queueName])['QueueUrl'];
    }
}
