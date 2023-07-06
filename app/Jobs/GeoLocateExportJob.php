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

use App\Models\GeoLocateForm;
use App\Models\User;
use App\Notifications\ExportNotification;
use App\Notifications\GeoLocateNotification;
use App\Notifications\JobError;
use App\Notifications\JobErrorNotification;
use App\Services\Csv\GeoLocateExportService;
use App\Services\Export\RapidExportFactoryType;
use App\Services\Export\RapidExportService;
use DB;
use Exception;
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
     * @var \App\Models\GeoLocateForm
     */
    private GeoLocateForm $form;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;


    /**
     * Create a new job instance.
     *
     * @param \App\Models\GeoLocateForm $form
     * @param \App\Models\User $user
     */
    public function __construct(GeoLocateForm $form, User $user)
    {
        $this->onQueue(config('config.queues.default'));
        $this->form = $form;
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
        try {
            $geoLocateExportService->setSourceType($this->form);
            $geoLocateExportService->migrateRecords($this->form);
            $geoLocateExportService->moveCsvFile($this->form);

            $file = route('admin.downloads.geolocate', ['file' => base64_encode($this->form->filePath)]);

            $this->user->notify(new GeoLocateNotification($file));

            return;

        } catch (Exception $exception) {
            $messages = [
                t('Error: %s', $exception->getMessage()),
                t('File: %s', $exception->getFile()),
                t('Line: %s', $exception->getLine()),
            ];

            $this->user->notify(new JobError(__FILE__, $messages));

            if (Storage::disk('s3')->exists($this->form->filePath)) {
                Storage::disk('s3')->delete($this->form->filePath);
            }

            if (Storage::disk('efs')->exists($this->form->filePath)) {
                Storage::disk('efs')->delete($this->form->filePath);
            }
        }
    }
}
