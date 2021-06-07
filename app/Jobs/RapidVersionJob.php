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

use App\Notifications\JobErrorNotification;
use App\Notifications\VersionNotification;
use App\Services\Export\RapidExportFactoryType;
use App\Services\Export\RapidExportService;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

/**
 * Class RapidVersionJob
 *
 * @package App\Jobs
 */
class RapidVersionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Export\RapidExportService $rapidExportService
     * @throws \Throwable
     */
    public function handle(RapidExportService $rapidExportService) {

        if (! Storage::exists(config('config.rapid_version_dir'))) {
            Storage::makeDirectory(config('config.rapid_version_dir'));
        }

        $now = Carbon::now('UTC')->timestamp;
        $fileName = $now.'.csv';
        $zipFileName = $now.'.zip';

        $filePath = $rapidExportService->getVersionFilePath($fileName);
        $zipFilePath = $rapidExportService->getVersionFilePath($zipFileName);

        DB::beginTransaction();

        try {

            $fields = $rapidExportService->buildVersionFields();
            $headerId = $rapidExportService->getHeaderId();
            $reservedColumns =$rapidExportService->getReservedColumns();

            $exportTypeClass = RapidExportFactoryType::create($fields['exportType']);
            $exportTypeClass->build($filePath, $fields, $reservedColumns);

            $rapidExportService->zipFile([$fileName => $filePath], $zipFilePath);

            $attributes = [
                'header_id' => $headerId,
                'user_id'   => $this->user->id,
                'file_name' => $zipFileName,
            ];
            $rapidExportService->createVersionRecord($attributes);

            $rapidExportService->deleteVersionFile($fileName);

            $downloadUrl = route('admin.download.version', ['file' => base64_encode($zipFileName)]);
            $this->user->notify(new VersionNotification($downloadUrl));

            DB::commit();

        } catch (\Exception $e) {
            $rapidExportService->deleteVersionFile($fileName);
            $rapidExportService->deleteVersionFile($zipFileName);

            DB::rollback();

            $attributes = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ];

            $this->user->notify(new JobErrorNotification($attributes));
        }
    }
}
