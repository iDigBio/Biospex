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

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Actor\ActorInterface;
use App\Services\Actor\Traits\ActorDirectory;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Services\Api\AwsLambdaApiService;
use Aws\Lambda\LambdaClient;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ZooniverseExportProcessImage
 */
class ZooniverseExportProcessImage  implements ActorInterface
{
    use ActorDirectory;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Services\Api\AwsLambdaApiService
     */
    private AwsLambdaApiService $awsLambdaApiService;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $queue;

    /**
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Services\Api\AwsLambdaApiService $awsLambdaApiService
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository,
        AwsLambdaApiService $awsLambdaApiService
    )
    {

        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->awsLambdaApiService = $awsLambdaApiService;
    }

    /**
     * Process images.
     *
     * @param \App\Models\Actor $actor
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $this->queue = $this->exportQueueRepository->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $this->queue->processed = 0;
        $this->queue->stage = 1;
        $this->queue->save();

        try {
            \Artisan::call('export:poll');

            $this->setFolder($this->queue->id, $actor->id, $this->queue->expedition->uuid);
            $this->setDirectories();

            $this->processLambdaImageExport();

        } catch (\Exception $exception) {
            $this->queue->error = 1;
            $this->queue->queued = 0;
            $this->queue->processed = 0;
            $this->queue->save();

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Chunk through export files and send to lambda function.
     *
     * @return void
     */
    public function processLambdaImageExport()
    {
        $callback = function ($files) {
            $files->reject(function ($file) {
                return $this->checkFileExistsAndUpdate($file);
            })->each(function ($file) {
                $data = $this->createDataArray($file);
                $this->awsLambdaApiService->lambdaInvokeAsync('imageExportProcess', $data);
            });

            sleep(config('config.aws_lambda_delay'));
        };

        $this->exportQueueFileRepository->chunkExportFiles($callback);
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
            'dir'       => $this->tmpDir,
        ];
    }

    /**
     * Check if file exists and update process.
     * Trigger polling if file exists.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return bool
     */
    private function checkFileExistsAndUpdate(ExportQueueFile $file): bool
    {
        $filePath = $this->tmpDir.'/'.$file->subject_id.'.jpg';

        if ($this->checkFileExists($filePath, $file->subject_id)){
            $this->queue->processed++;
            $this->queue->save();

            $file->completed = 1;
            $file->save();

            \Artisan::call('export:poll');

            return true;
        }
        return false;
    }
}