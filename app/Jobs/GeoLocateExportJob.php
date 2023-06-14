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
use App\Notifications\ExportNotification;
use App\Notifications\JobErrorNotification;
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
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $filePath;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param array $data
     */
    public function __construct(User $user, array $data)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Export\RapidExportService $rapidExportService
     * @throws \Throwable
     */
    public function handle(RapidExportService $rapidExportService)
    {
        DB::beginTransaction();

        try {

            $fields = $rapidExportService->getMappedFields($this->data);

            $form = $rapidExportService->saveForm($fields, $this->user->id);
            $fileName = $rapidExportService->createFileName($form, $this->user, $fields);
            $this->filePath = $rapidExportService->getExportFilePath($fileName);

            $reservedColumns =$rapidExportService->getReservedColumns();

            $exportTypeClass = RapidExportFactoryType::create($fields['exportType']);
            $exportTypeClass->build($this->filePath, $fields, $reservedColumns);

            $downloadUrl = route('admin.download.export', ['file' => base64_encode($fileName)]);

            DB::commit();

            $this->user->notify(new ExportNotification($downloadUrl));

            return;

        } catch (Exception $exception) {
            $attributes = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));

            DB::rollback();

            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}
