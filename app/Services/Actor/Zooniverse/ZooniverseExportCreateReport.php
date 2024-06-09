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

use App\Jobs\ZooniverseExportDeleteFilesJob;
use App\Models\ExportQueue;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Repositories\ExportQueueFileRepository;
use App\Services\Actor\QueueInterface;
use App\Services\Process\CreateReportService;
use Notification;

/**
 * Class ZooniverseExportCreateReport
 *
 * @package App\Services\Actor
 */
class ZooniverseExportCreateReport implements QueueInterface
{
    use ButtonTrait;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Services\Process\CreateReportService
     */
    private CreateReportService $createReportService;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function __construct(
        ExportQueueFileRepository $exportQueueFileRepository,
        CreateReportService $createReportService
    )
    {
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->createReportService = $createReportService;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue)
    {
        $exportQueue->load([
            'expedition.project.group' => function($q) {
                $q->with(['owner', 'users' => function($q){
                    $q->where('notification', 1);
                }]);
            }
        ]);

        $data = $this->exportQueueFileRepository->getQueueFileErrorsData($exportQueue->id);

        $csvName = $exportQueue->expedition->uuid.'.csv';
        $fileName = $this->createReportService->createCsvReport($csvName, $data);

        if ($fileName) {
            $this->createReportService->saveReport($exportQueue, $csvName);
        }

        $route = route('admin.downloads.report', ['file' => $fileName]);
        $button = $this->createButton($route, t('Download Export Errors'), 'error');

        $attributes = [
            'subject' => t('Zooniverse Export Completed'),
            'html'    => [
                t('The export process for "%s" has been completed successfully.', $exportQueue->expedition->title),
                t('If a download file was created during this process, you may access the link on the Expedition view page.')
            ],
            'buttons' => [$button]
        ];

        $users = $exportQueue->expedition->project->group->users->push($exportQueue->expedition->project->group->owner);

        Notification::send($users, new Generic($attributes));

        $exportQueue->processed = 0;
        $exportQueue->stage = 7;
        $exportQueue->save();

        \Artisan::call('export:poll');

        ZooniverseExportDeleteFilesJob::dispatch($exportQueue);
    }
}