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
 * Class ZooniverseBuildTar
 *
 * @package App\Services\Actor
 */
class ZooniverseBuildTar extends ZooniverseBase implements ActorInterface
{
    /**
     * @var \App\Services\Actor\ZooniverseDbService
     */
    private $dbService;

    /**
     * ZooniverseBuildTar constructor.
     *
     * @param \App\Services\Actor\ZooniverseDbService $dbService
     */
    public function __construct(
        ZooniverseDbService $dbService
    ) {

        $this->dbService = $dbService;
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

        $this->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
        $this->setDirectories();

        $tmpTar = '/tmp/'.$this->folderName.'.tar';
        $tmpGz = '/tmp/'.$this->folderName.'.tar.gz';

        try {
            $this->deleteFile($this->archiveExportPath);
            $this->deleteFile($tmpTar);
            $this->deleteFile($tmpGz);

            $archive = new \PharData($tmpTar);
            $archive->buildFromIterator(new \DirectoryIterator($this->tmpDirectory), $this->tmpDirectory);
            $archive->compress(\Phar::GZ);

            $this->deleteFile($tmpTar);

            if (! \File::move($tmpGz, $this->archiveExportPath)) {
                throw new \Exception(t('Unable to move compressed file to export directory.'));
            }

            $values = [
                'expedition_id' => $queue->expedition->id,
                'actor_id'      => $actor->id,
                'file'          => $this->archiveTarGz,
                'type'          => 'export',
            ];
            $attributes = [
                'expedition_id' => $queue->expedition->id,
                'actor_id'      => $actor->id,
                'file'          => $this->archiveTarGz,
                'type'          => 'export',
            ];

            $this->dbService->downloadService->updateOrCreate($attributes, $values);

            $queue->processed = 0;
            $queue->stage = 6;
            $queue->save();

        } catch (\Exception $exception) {
            $this->deleteFile($this->archiveExportPath);
            $this->deleteFile($tmpTar);
            $this->deleteFile($tmpGz);

            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }
    }
}