<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Event\EventService;
use App\Services\Process\CreateReportService;
use App\Traits\ButtonTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Str;
use Throwable;

/**
 * Class EventUserExportCsvJob
 */
class EventUserExportCsvJob implements ShouldQueue
{
    use ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user, protected Event $event)
    {
        $this->user = $user->withoutRelations();
        $this->event = $event->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(
        EventService $eventService,
        CreateReportService $createReportService,
    ): void {
        try {
            $eventService->getAdminShow($this->event);
            $rows = $this->event->teams->flatMap(function ($team) {
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
                'html' => [
                    t('Your export is completed. If a report was generated, you may click the download button to download the file. If no button is included, it is due to no records being located for the export. Some records require overnight processing before they are available.'),
                    t('If you believe this is an error, please contact the Administration.'),
                ],
                'buttons' => $fileButton,
            ];

            $this->user->notify(new Generic($attributes));

        } catch (Throwable $throwable) {
            $attributes = [
                'subject' => t('Event User Export Error'),
                'html' => [
                    t('There was an error while exporting the csv file. The Administration has been copied on this error and will investigate.'),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                ],
            ];
            $this->user->notify(new Generic($attributes, true));
        }
    }
}
