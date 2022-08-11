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
use App\Services\Actor\ActorInterface;
use App\Services\Actor\Traits\ActorDirectory;
use App\Repositories\DownloadRepository;
use App\Repositories\ExportQueueRepository;

/**
 * Class ZooniverseBuildZip
 *
 * @package App\Services\Actor
 */
class ZooniverseBuildZip implements ActorInterface
{
    use ActorDirectory;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepository;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\DownloadRepository $downloadRepository
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        DownloadRepository $downloadRepository
    )
    {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * Process the actor.
     *
     * @param \App\Models\Actor $actor
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->exportQueueRepository->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 4;
        $queue->save();

        \Artisan::call('export:poll');

        $this->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
        $this->setDirectories();

        try {
            $this->deleteFile($this->archiveZipPath);

            $output=null;
            $retval=null;
            $path = config('config.current_path') . '/zipExport.js';
            exec("node $path {$this->tmpDir} {$this->archiveZipPath}" , $output, $retval);

            if ($retval) {
                throw new \Exception($output);
            }

            $values = [
                'expedition_id' => $queue->expedition->id,
                'actor_id'      => $actor->id,
                'file'          => $this->archiveZip,
                'type'          => 'export',
            ];
            $attributes = [
                'expedition_id' => $queue->expedition->id,
                'actor_id'      => $actor->id,
                'file'          => $this->archiveZip,
                'type'          => 'export',
            ];

            $this->downloadRepository->updateOrCreate($attributes, $values);

            $this->cleanDirectory(config('config.aws_efs_dir'));

        } catch (\Exception $exception) {
            $this->deleteFile($this->archiveZipPath);

            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }
    }
}