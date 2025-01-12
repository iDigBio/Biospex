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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\ActorExpedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\GeoLocate\GeoLocateResultCsvService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Job class responsible for handling the creation and processing of a GeoLocate result CSV file for a given actor expedition.
 *
 * This job interacts with remote storage (e.g., S3) to validate and process the CSV source file
 * and updates system records to reflect the outcome of the GeoLocation process.
 * It also handles potential job failures by notifying an administrative user.
 */
class GeoLocateResultCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Constructor method for the class.
     *
     * @param  ActorExpedition  $actorExpedition  An instance of the ActorExpedition object.
     * @return void
     */
    public function __construct(protected ActorExpedition $actorExpedition)
    {
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * Handles the geolocation process using the given service.
     *
     * @param  GeoLocateResultCsvService  $service  An instance of the service responsible for processing geolocation CSV files.
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     * @throws \League\Csv\SyntaxError
     */
    public function handle(GeoLocateResultCsvService $service): void
    {
        \Log::info('GeoLocateResultCsv: '.$this->actorExpedition->expedition->title);

        $this->actorExpedition->load(['expedition' => function ($q) {
            $q->with('geoLocateForm', 'geoLocateCsvDownload');
        }]);

        $sourceFile = config('geolocate.dir.csv').'/'.$this->actorExpedition->expedition->geoLocateCsvDownload->file;

        if (! Storage::disk('s3')->exists($sourceFile)) {
            \Log::error('GeoLocateResultCsv: File not found: '.$sourceFile);
            $this->failed(new \Exception('File not found'));
        }

        $service->processCsvDownload($sourceFile, $this->actorExpedition->expedition->geoLocateForm->fields);

        $destinationFile = config('geolocate.dir.geo-reconciled').'/'.$this->actorExpedition->expedition_id.'.csv';
        $service->createUpdateGeoReconciledDownload($destinationFile, $this->actorExpedition->expedition_id);

        $service->createOrUpdateDownload($this->actorExpedition->expedition_id);
    }

    /**
     * Handles the failure of the GeoLocate result CSV creation process.
     *
     * @param  Throwable  $throwable  An instance of Throwable containing details of the error that occurred.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('GeoLocate Result CSV Failed'),
            'html' => [
                t('The GeoLocate result csv creation has failed for %s', $this->actorExpedition->expedition->title),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified.'),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
