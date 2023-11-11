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

use App\Models\Expedition;
use App\Models\User;
use App\Notifications\GeoLocateExportNotification;
use App\Notifications\JobError;
use App\Services\Csv\GeoLocateExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class RapidExportJob
 *
 * @package App\Jobs
 */
class GeoLocateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Expedition
     */
    private Expedition $expedition;

    /**
     * @var \App\Models\User
     */
    private User $user;

    /**
     * @var \App\Services\Csv\GeoLocateExportService
     */
    private GeoLocateExportService $geoLocateExportService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Expedition $expedition
     * @param \App\Models\User $user
     */
    public function __construct(Expedition $expedition, User $user)
    {
        $this->onQueue(config('config.queue.default'));
        $this->expedition = $expedition;
        $this->user = $user;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Csv\GeoLocateExportService $geoLocateExportService
     * @throws \Throwable
     */
    public function handle(GeoLocateExportService $geoLocateExportService): void
    {
        $this->geoLocateExportService = $geoLocateExportService;

        $this->expedition->load('geoLocateForm');

        $geoLocateExportService->migrateRecords($this->expedition);

        $geoLocateExportService->setCsvFilePath($this->expedition->id);

        $geoLocateExportService->build($this->expedition);
        $csvFilePath = $geoLocateExportService->moveCsvFile();
        $geoLocateExportService->createDownload($this->expedition);

        $this->expedition->actors()->updateExistingPivot(config('config.geolocate.actor_id'), [
            'state' => 1,
        ]);

        $file = route('admin.downloads.geolocate', ['file' => base64_encode($csvFilePath)]);

        $this->user->notify(new GeoLocateExportNotification($file));
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $this->expedition->actors()->updateExistingPivot(config('config.geolocate.actor_id'), [
            'state' => 0,
        ]);

        $csvFilePath = $this->geoLocateExportService->getCsvFilePath();

        if (Storage::disk('s3')->exists($csvFilePath)) {
            Storage::disk('s3')->delete($csvFilePath);
        }

        if (Storage::disk('efs')->exists($csvFilePath)) {
            Storage::disk('efs')->delete($csvFilePath);
        }

        $messages = [
            'Error: '.t('Could not export GeoLocate data for Expedition %s', $this->expedition->title),
            t('Error: %s', $throwable->getMessage()),
            t('File: %s', $throwable->getFile()),
            t('Line: %s', $throwable->getLine()),
        ];

        $this->user->notify(new JobError(__FILE__, $messages));
    }
}
