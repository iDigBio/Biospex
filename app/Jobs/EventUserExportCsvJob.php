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

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Services\Models\EventModel;
use App\Services\Process\CreateReportService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Str;

/**
 * Class EventUserExportCsvJob
 *
 * @package App\Jobs
 */
class EventUserExportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ButtonTrait;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var User
     */
    private $user;

    /**
     * @var
     */
    private $eventId;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param null $eventId
     */
    public function __construct(User $user, $eventId)
    {
        $this->user = $user;
        $this->eventId = $eventId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Models\EventModel $eventModel
     * @param \App\Services\Process\CreateReportService $createReportService
     * @return void
     */
    public function handle(
        EventModel $eventModel,
        CreateReportService $createReportService,
    ) {
        try {
            $event = $eventModel->getShow($this->eventId);
            $rows = $event->teams->flatMap(function ($team) {
                return $team->users->map(function ($user) use ($team) {
                    return [
                        'Team' => $team->title,
                        'User' => $user->nfn_user,
                        'Transcriptions' => $user->transcriptions_count,
                    ];
                });
            })->toArray();

            $csvName = Str::random().'.csv';
            $fileName = $createReportService->createCsvReport($csvName, $rows);
            $fileButton = [];
            if ($fileName !== null) {
                $fileRoute = route('admin.downloads.report', ['file' => $fileName]);
                $fileButton = $this->createButton($fileRoute, t('Download CSV'));
            }

            $attributes = [
                'subject' => t('Event User Export Complete'),
                'html'    => [
                    t('Your export is completed. If a report was generated, you may click the download button to download the file. If no button is included, it is due to no records being located for the export. Some records require overnight processing before they are available.'),
                    t('If you believe this is an error, please contact the Administration.')
                ],
                'buttons' => $fileButton
            ];

            $this->user->notify(new Generic($attributes));

        } catch (Exception $e) {
            $attributes = [
                'subject' => t('Event User Export Error'),
                'html'    => [
                    t('There was an error while exporting the csv file. The Administration has been copied on this error and will investigate.'),
                    t('File: %s', $e->getFile()),
                    t('Line: %s', $e->getLine()),
                    t('Message: %s', $e->getMessage())
                ]
            ];
            $this->user->notify(new Generic($attributes, true));
        }
    }
}
