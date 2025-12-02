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

use App\Models\Expedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\GeoLocate\GeoLocateExportService;
use App\Traits\ButtonTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * A job responsible for exporting GeoLocate data for a given expedition.
 *
 * This job queues and processes the GeoLocate data export, prepares the necessary data,
 * and sends notifications to the user upon success or failure.
 *
 * Implements the ShouldQueue interface to allow asynchronous job handling.
 */
class GeoLocateExportJob implements ShouldQueue
{
    use ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The timeout duration in seconds.
     */
    public int $timeout = 1800;

    /**
     * Constructor for initializing the Expedition and User instances.
     *
     * @param  Expedition  $expedition  The expedition instance, relationships excluded.
     * @param  User  $user  The user instance, relationships excluded.
     * @return void
     */
    public function __construct(protected Expedition $expedition, protected User $user)
    {
        $this->expedition = $expedition->withoutRelations();
        $this->user = $user->withoutRelations();
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * Handles the processing of a GeoLocate CSV export and notifies the user.
     *
     * @param  GeoLocateExportService  $geoLocateExportService  The service responsible for handling the GeoLocate export process.
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     * @throws \Throwable
     */
    public function handle(GeoLocateExportService $geoLocateExportService): void
    {
        $this->expedition->load(['geoLocateDataSource' => function ($query) {
            $query->with(['geoLocateForm', 'download']);
        }]);

        $geoLocateExportService->process($this->expedition);

        $attributes = [
            'subject' => t('GeoLocateExport Csv Export'),
            'html' => [
                t('Your GeoLocate csv export is completed. Please visit the Expedition Download section to download.'),
            ],
        ];

        $this->user->notify(new Generic($attributes));
    }

    /**
     * Handles failures by processing the given throwable and notifying the user.
     *
     * @param  \Throwable  $throwable  The throwable instance containing error details.
     */
    public function failed(\Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('GeoLocate Form Export Error'),
            'html' => [
                'Error: '.$throwable->getMessage(),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];

        $this->user->notify(new Generic($attributes, true));
    }
}
