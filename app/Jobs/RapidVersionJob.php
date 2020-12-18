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
use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidVersionModelService;
use App\Models\User;
use App\Services\RapidServiceBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use Throwable;

/**
 * Class RapidVersionJob
 *
 * @package App\Jobs
 */
class RapidVersionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var string
     */
    private $versionFileName;

    /**
     * @var \App\Services\RapidServiceBase
     */
    private $rapidServiceBase;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param int $timestamp
     */
    public function __construct(User $user, int $timestamp)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
        $this->versionFileName = $timestamp.'.csv';
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidServiceBase $rapidServiceBase
     * @param \App\Services\Model\RapidVersionModelService $rapidVersionModelService
     * @param \App\Services\Model\RapidHeaderModelService $rapidHeaderModelService
     */
    public function handle(
        RapidServiceBase $rapidServiceBase,
        RapidVersionModelService $rapidVersionModelService,
        RapidHeaderModelService $rapidHeaderModelService
    ) {
        if (! Storage::exists(config('config.rapid_version_dir'))) {
            Storage::makeDirectory(config('config.rapid_version_dir'));
        }

        $this->rapidServiceBase = $rapidServiceBase;

        try {

            $versionFilePath = $rapidServiceBase->getVersionFilePath($this->versionFileName);
            $header = $rapidHeaderModelService->getLatestHeader();
            $rapidServiceBase->buildExportHeader($header->data);
            $exportHeaderPath = $rapidServiceBase->getExportHeaderFile();
            $dbHost = config('database.connections.mongodb.host');

            exec('mongoexport --host='.$dbHost.' --quiet --db=rapid --collection=rapid_records --type=csv --fieldFile='.$exportHeaderPath.' --out='.$versionFilePath, $output, $result_code);

            if (! $result_code) {
                throw new \Exception(t('Error in executing command to build version file %s', $this->versionFileName));
            }

            $size = $rapidServiceBase->getVersionFileSize($this->versionFileName);

            if (! $size) {
                throw new \Exception(t('Version file was empty for file %s', $this->versionFileName));
            }

            $rapidVersionModelService->create([
                'header_id' => $header->id,
                'user_id'   => $this->user->id,
                'file_name' => $this->versionFileName,
            ]);

            $rapidServiceBase->deleteExportHeaderFile();

            $downloadUrl = route('admin.download.version', [base64_encode($this->versionFileName)]);
            $this->user->notify(new VersionNotification($downloadUrl));
        } catch (\Exception $e) {
            $rapidServiceBase->deleteVersionFile($this->versionFileName);
            $rapidServiceBase->deleteExportHeaderFile();

            $attributes = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ];

            $this->user->notify(new JobErrorNotification($attributes));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->rapidServiceBase->deleteVersionFile($this->versionFileName);
        $this->rapidServiceBase->deleteExportHeaderFile();

        $attributes = [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ];

        $this->user->notify(new JobErrorNotification($attributes));
    }
}
