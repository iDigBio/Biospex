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

namespace App\Services\Actor;

use App\Models\Actor;

/**
 * Class ZooniverseConvertImage
 *
 * @package App\Services\Actor
 */
class ZooniverseConvertImage extends ZooniverseBase implements ActorInterface
{
    /**
     * @var \App\Services\Actor\ZooniverseDbService
     */
    private $dbService;

    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private $actorImageService;

    /**
     * ZooniverseConvertImage constructor.
     *
     * @param \App\Services\Actor\ZooniverseDbService $dbService
     * @param \App\Services\Actor\ActorImageService $actorImageService
     */
    public function __construct(
        ZooniverseDbService $dbService,
        ActorImageService $actorImageService
    )
    {
        $this->dbService = $dbService;
        $this->actorImageService = $actorImageService;
    }

    /**
     * Process the actor.
     *
     * @param \App\Models\Actor $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueService->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 2;
        $queue->save();

        $files = $this->dbService->exportQueueFileService->getFilesByQueueId($queue->id);

        $this->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
        $this->setDirectories();

        try {
            $files->chunk(5)->each(function ($chunk) use (&$queue) {
                $chunk->reject(function ($file) {
                    return $this->actorImageService->checkFile($this->tmpDirectory . '/' . $file->subject_id . '.jpg');
                })->each(function ($file) {
                    $filePath = $this->workingDirectory . '/' . $file->subject_id . '.jpg';
                    $this->actorImageService->processFileImage($filePath, $this->tmpDirectory, $file->subject_id);
                });

                $queue->processed = $queue->processed + $chunk->count();
                $queue->save();
            });

            $this->dbService->updateRejected($this->actorImageService->getRejected());

        } catch (\Exception $exception) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }

    }
}