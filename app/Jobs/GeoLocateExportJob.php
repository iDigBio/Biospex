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
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Services\Actor\GeoLocate\GeoLocateExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RapidExportJob
 */
class GeoLocateExportJob implements ShouldQueue
{
    use ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Expedition $expedition, protected User $user)
    {
        $this->expedition = $expedition->withoutRelations();
        $this->user = $user->withoutRelations();
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * Execute job.
     *
     * @throws \Throwable
     */
    public function handle(GeoLocateExportService $geoLocateExportService): void
    {
        $this->expedition->load('geoLocateForm');

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
     * Handle a job failure.
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
