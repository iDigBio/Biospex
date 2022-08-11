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
use App\Notifications\NfnExportComplete;
use App\Services\Actor\ActorInterface;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Services\Process\CreateReportService;
use Notification;

/**
 * Class ZooniverseExportCreateReport
 *
 * @package App\Services\Actor
 */
class ZooniverseExportCreateReport implements ActorInterface
{
    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Services\Process\CreateReportService
     */
    private CreateReportService $createReportService;

    /**
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository,
        ExpeditionRepository $expeditionRepository,
        CreateReportService $createReportService
    )
    {

        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->expeditionRepository = $expeditionRepository;
        $this->createReportService = $createReportService;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->exportQueueRepository->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 5;
        $queue->save();

        \Artisan::call('export:poll');

        try {
            $data = $this->exportQueueFileRepository->getQueueFileErrorsData($queue->id);

            $csvName = md5($queue->id).'.csv';
            $fileName = $this->createReportService->createCsvReport($csvName, $data);
            $this->createReportService->saveReport($queue, $csvName);

            $expedition = $this->expeditionRepository->findNotifyExpeditionUsers($queue->expedition_id);
            $users = $expedition->project->group->users->push($expedition->project->group->owner);

            Notification::send($users, new NfnExportComplete($expedition->title, $fileName));

        } catch (\Exception $exception) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }
    }
}