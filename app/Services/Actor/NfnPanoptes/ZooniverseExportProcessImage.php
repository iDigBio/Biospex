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

/**
 * Class ZooniverseExportProcessImage
 */
class ZooniverseExportProcessImage extends ZooniverseBase implements ActorInterface
{
    /**
     * Process images.
     *
     * @param \App\Models\Actor $actor
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueRepo->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 1;
        $queue->save();

        try {
            \Artisan::call('export:poll');

            $files = $this->dbService->exportQueueFileRepo->getFilesByQueueId($queue->id);

            $this->actorDirectory->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
            $this->actorDirectory->setDirectories();

            $files->each(function ($file) use (&$queue) {
                $this->processImageFile($file, $queue);
            });

            $this->dbService->updateRejected($this->rejected);

        } catch (\Exception $exception) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Set image vars.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return array
     */
    private function setImageVars(ExportQueueFile $file): array
    {
        return [
            str_replace(' ', '%20', $file->url),
            base_path('image-process.js'),
            $file->subject_id.'.jpg',
        ];
    }

    /**
     * Process each file and convert image.
     *
     * @param \App\Models\ExportQueueFile $file
     * @param \App\Models\ExportQueue $queue
     * @return void
     */
    private function processImageFile(ExportQueueFile $file, ExportQueue $queue)
    {
        try {
            [$url, $basePath, $fileName] = $this->setImageVars($file);

            if (\File::exists($this->actorDirectory->tmpDirectory.'/'.$fileName)) {
                $queue->processed = $queue->processed + 1;
                $queue->save();

                return;
            }

            $output = null;
            exec("node $basePath $fileName $url {$this->actorDirectory->tmpDirectory} {$this->nfnImageWidth} {$this->nfnImageHeight}", $output);

            if ($output[0]) {
                $queue->processed = $queue->processed + 1;
                $queue->save();

                return;
            }

            $this->rejected[$fileName] = $output[0];
        }catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}