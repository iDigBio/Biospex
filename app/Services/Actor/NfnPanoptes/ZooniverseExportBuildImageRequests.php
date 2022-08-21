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

namespace App\Services\Actor\NfnPanoptes;

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
    private ExportQueue $queue;

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
     * @param \App\Models\ExportQueue $queue
     * @return void
     * @throws \Exception
     */
    public function process(ExportQueue $queue)
    {
        $queue->load(['expedition']);
        $queue->processed = 0;
        $queue->stage = 1;
        $queue->save();

        //\Artisan::call('export:poll');

        $this->setFolder($queue->id, $queue->actor->id, $queue->expedition->uuid);
        $this->setDirectories();

        $lambdaCount = config('config.aws_lambda_count');
        $total = $this->exportQueueFileRepository->getExportFilesCount($queue->id);

        $multiplier = 0;
        $this->exportQueueFileRepository->model()
            ->where('queue_id', $queue->id)
            ->chunk($lambdaCount, function ($files) use ($queue, &$multiplier, &$total) {
                $total = $total - $files->count();

                $data = $files->reject(function ($file) use ($queue) {
                    return $this->checkFileExistsAndUpdate($queue, $file);
                })->map(function ($file) {
                    return $this->createDataArray($file);
                });

                $queue->processed = $queue->processed + $data->count();
                $queue->save();

                $multiplier++;
                $delay = $multiplier === 1 ? 0 : $multiplier * 30;

                \Log::alert('Processed ' . $queue->processed);
                \Log::alert('Adding delay ' . $delay);
                \Log::alert('Total Left ' . $total);

                ZooniverseExportLambdaJob::dispatch($queue, $data, $total === 0)->delay($delay);
        });
    }

    /**
     * Create data array.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return array
     */
    #[ArrayShape(['queueId' => "mixed", 'subjectId' => "mixed", 'url' => "mixed", 'dir' => "string"])]
    private function createDataArray(ExportQueueFile $file): array
    {
        return [
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
     * @param \App\Models\ExportQueue $queue
     * @param \App\Models\ExportQueueFile $file
     * @return bool
     */
    private function checkFileExistsAndUpdate(ExportQueue $queue, ExportQueueFile $file): bool
    {
        $filePath = $this->workingDir.'/'.$file->subject_id.'.jpg';

        if ($this->checkFileExists($filePath, $file->subject_id)) {
            $queue->processed++;
            $queue->save();

            $file->completed = 1;
            $file->save();

            return true;
        }

        return false;
    }
}