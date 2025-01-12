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
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\GeoLocate\GeoLocateDownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class GeoLocateDownloadJob
 */
class GeoLocateDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ActorExpedition $actorExpedition,
        protected string $community,
        protected string $dataSource)
    {
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * Manages the download process for multiple format types using the GeoLocateDownloadService
     * and dispatches the result processing.
     *
     * @param  GeoLocateDownloadService  $service  An instance of the GeoLocateDownloadService for handling the download operations.
     */
    public function handle(GeoLocateDownloadService $service): void
    {
        // Download kml and csv files.
        collect(['kml', 'csv'])->each(function ($formatType) use ($service) {
            $this->getDownload($service, $formatType);
        });

        GeoLocateResultCsv::dispatch($this->actorExpedition);
    }

    /**
     * Handles the download process for a specified format type using the GeoLocateDownloadService.
     *
     * @param  GeoLocateDownloadService  $service  An instance of the GeoLocateDownloadService to manage the download process.
     * @param  string  $formatType  The format type of the file to be downloaded (e.g., 'kml').
     */
    private function getDownload(GeoLocateDownloadService $service, string $formatType): void
    {
        try {
            $options = $formatType === 'kml' ? '' : '&rec=spm|skp|cor|lst';
            $service->setFormatType($formatType);
            $service->setCsvOptions($options);
            $service->downloadFile($this->actorExpedition->expedition_id, $this->community, $this->dataSource);
            $service->saveDownload($this->actorExpedition);
        } catch (Throwable $throwable) {
            $expedition = Expedition::find($this->actorExpedition->expedition_id);
            $subject = t('GeoLocate :format download for :title failed.', [':format' => $formatType, ':title' => $expedition->title]);
            $attributes = [
                'subject' => $subject,
                'html' => [
                    t('Error: %s', $subject),
                    t('Error: %s', $throwable->getMessage()),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                ],
            ];

            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes));
        }
    }
}
