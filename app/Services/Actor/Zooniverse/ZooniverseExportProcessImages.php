<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Actor\Zooniverse;

use App\Jobs\ZooniverseExportBuildCsvJob;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Actor\ActorDirectory;
use App\Services\Api\AwsLambdaApiService;

class ZooniverseExportProcessImages
{
    /**
     * ZooniverseExportProcessImages constructor.
     */
    public function __construct(
        protected ExportQueueFile $exportQueueFile,
        protected AwsLambdaApiService $awsLambdaApiService
    ) {}

    /**
     * Process export queue files.
     */
    public function process(ExportQueue $exportQueue, ActorDirectory $actorDirectory): void
    {
        $files = $this->exportQueueFile->where('queue_id', $exportQueue->id)
            ->where('processed', 0)
            ->orderBy('id')
            ->take(config('config.aws.lambda_export_count'))->get();

        // If processed files count is 0, send to csv job.
        if ($files->count() === 0) {
            ZooniverseExportBuildCsvJob::dispatch($exportQueue, $actorDirectory);

            return;
        }

        $files->each(function ($file) use ($actorDirectory) {
            // Some delay in processing from lambda to database so check if file exists in s3.
            if ($actorDirectory->checkS3FileExists($actorDirectory->workingDir.'/'.$file->subject_id.'.jpg')) {
                $file->processed = 1;
                $file->save();

                return;
            }

            if ($file->tries < 3) {
                $file->increment('tries');
                $data = $this->createDataArray($file, $actorDirectory->workingDir);
                $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_export_function'), $data);

                return;
            }

            $file->processed = 1;
            $file->message = is_null($file->message) ? t('Error: Exceeded maximum tries sending to Lambda for download.') : $file->message;
            $file->save();
        });

        $exportQueue->queued = 0;
        $exportQueue->save();
    }

    /**
     * Create data array.
     */
    private function createDataArray(ExportQueueFile $file, string $workingDir): array
    {
        return [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'queueId' => $file->queue_id,
            'subjectId' => $file->subject_id,
            'accessUri' => $file->access_uri,
            'dir' => $workingDir,
        ];
    }
}
