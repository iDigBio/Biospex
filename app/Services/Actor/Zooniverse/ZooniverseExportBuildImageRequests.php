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

use App\Jobs\ZooniverseExportLambdaJob;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Actor\QueueInterface;
use App\Services\Actor\Traits\ActorDirectory;
use App\Repositories\ExportQueueFileRepository;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ZooniverseExportBuildImageRequests
 */
class ZooniverseExportBuildImageRequests implements QueueInterface
{
    use ActorDirectory;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     */
    public function __construct(
        ExportQueueFileRepository $exportQueueFileRepository
    ) {
        $this->exportQueueFileRepository = $exportQueueFileRepository;
    }

    /**
     * Process images.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue)
    {
        $exportQueue->load(['expedition']);

        $this->setFolder($exportQueue->id, $exportQueue->actor->id, $exportQueue->expedition->uuid);
        $this->setDirectories();

        $lambdaCount = config('config.aws.lambda_count');
        $total = $this->exportQueueFileRepository->getExportFilesCount($exportQueue->id);

        $multiplier = 0;
        $this->exportQueueFileRepository->model()
            ->where('queue_id', $exportQueue->id)
            ->chunk($lambdaCount, function ($files) use ($exportQueue, &$multiplier, &$total) {
                $total = $total - $files->count();

                $data = $files->reject(function ($file) use ($exportQueue) {
                    return $this->checkFileExistsAndUpdate($exportQueue, $file);
                })->map(function ($file) {
                    return $this->createDataArray($file);
                });

                $exportQueue->processed = $exportQueue->processed + $data->count();
                $exportQueue->save();

                $delay = $multiplier * config('config.aws.lambda_delay');
                $multiplier++;

                ZooniverseExportLambdaJob::dispatch($exportQueue, $data, $total === 0)->delay($delay);
        });

        $exportQueue->processed = 0;
        $exportQueue->stage = 2;
        $exportQueue->save();

        \Artisan::call('export:poll');
    }

    /**
     * Create data array.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return array
     */
    private function createDataArray(ExportQueueFile $file): array
    {
        return [
            'bucket'    => config('filesystems.disks.s3.bucket'),
            'queueId'   => $file->queue_id,
            'subjectId' => $file->subject_id,
            'url'       => $file->url,
            'dir'       => $this->workingDir,
        ];
    }

    /**
     * Check if file exists and update process.
     * Trigger polling if file exists.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \App\Models\ExportQueueFile $file
     * @return bool
     */
    private function checkFileExistsAndUpdate(ExportQueue $exportQueue, ExportQueueFile $file): bool
    {
        $filePath = $this->workingDir.'/'.$file->subject_id.'.jpg';

        if ($this->checkFileExists($filePath, $file->subject_id)) {
            $exportQueue->processed++;
            $exportQueue->save();

            $file->completed = 1;
            $file->save();

            return true;
        }

        return false;
    }
}