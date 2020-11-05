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

use App\Facades\ActorEventHelper;
use App\Notifications\NfnExportComplete;
use App\Services\Model\DownloadService;
use App\Services\Model\ExportQueueService;
use App\Services\Model\ExportQueueFileService;
use App\Services\Csv\Csv;
use File;
use Notification;

/**
 * Class NfnPanoptesExportReport
 *
 * @see \App\Services\Actor\NfnPanoptes::processQueue()
 * @package App\Services\Actor
 */
class NfnPanoptesExportReport extends NfnPanoptesBase
{
    /**
     * @var \App\Services\Model\ExportQueueService
     */
    private $exportQueueService;

    /**
     * @var \App\Services\Model\ExportQueueFileService
     */
    private $exportQueueFileService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var \App\Services\Model\DownloadService
     */
    private $downloadService;

    /**
     * NfnPanoptesExportReport constructor.
     *
     * @param \App\Services\Model\ExportQueueService $exportQueueService
     * @param \App\Services\Model\ExportQueueFileService $exportQueueFileService
     * @param \App\Services\Csv\Csv $csv
     * @param \App\Services\Model\DownloadService $downloadService
     */
    public function __construct(
        ExportQueueService $exportQueueService,
        ExportQueueFileService $exportQueueFileService,
        Csv $csv,
        DownloadService $downloadService
    )
    {
        $this->exportQueueService = $exportQueueService;
        $this->exportQueueFileService = $exportQueueFileService;
        $this->csv = $csv;
        $this->downloadService = $downloadService;
    }

    /**
     * Send notification and clean up directories.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \Exception
     */
    public function process(\App\Models\ExportQueue $queue)
    {
        $this->setQueue($queue);
        $this->setExpedition($queue->expedition);
        $this->setActor($queue->expedition->actors->first());
        $this->setOwner($queue->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories();

        File::deleteDirectory($this->tmpDirectory);
        File::deleteDirectory($this->workingDirectory);

        $queue->delete();
        event('exportQueue.updated');

        ActorEventHelper::fireActorStateEvent($this->actor);
        ActorEventHelper::fireActorUnQueuedEvent($this->actor);

        $this->notify();
    }

    /**
     * Send notify for process completed.
     *
     * @throws \Exception
     */
    protected function notify()
    {
        $files = $this->exportQueueFileService->getFilesWithErrorsByQueueId($this->queue->id);
        $remove = array_flip(['id', 'queue_id', 'error', 'created_at', 'updated_at']);
        $data = $files->map(function($file) use($remove){
            return array_diff_key($file->toArray(), $remove);
        });

        $csvName = md5($this->queue->id).'.csv';
        $fileName = $this->csv->createReportCsv($data->toArray(), $csvName);

        if(isset($fileName)) {
            $this->saveReport($csvName);
        }

        $users = $this->expedition->project->group->users->push($this->owner);

        Notification::send($users, new NfnExportComplete($this->expedition->title, $fileName));
    }

    /**
     * Save report.
     *
     * @param string $csvName
     */
    private function saveReport(string $csvName)
    {
        $attributes = [
            'expedition_id' => $this->expedition->id,
            'actor_id' => $this->actor->id,
            'type' => 'report'
        ];
        $values = [
            'expedition_id' => $this->expedition->id,
            'actor_id' => $this->actor->id,
            'file' => $csvName,
            'type' => 'report'
        ];

        $this->downloadService->updateOrCreate($attributes, $values);
    }
}