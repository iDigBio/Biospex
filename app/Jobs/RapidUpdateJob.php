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
use App\Notifications\JobErrorNotification;
use App\Notifications\UpdateNotification;
use App\Services\Model\RapidUpdateService;
use App\Services\RapidIngestService;
use Exception;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RapidUpdateJob
 *
 * @package App\Jobs
 */
class RapidUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $fileOrigName;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1200;

    /**
     * Update rapid records job.
     *
     * @param \App\Models\User $user
     * @param string $filePath
     * @param string $fileOrigName
     */
    public function __construct(User $user, string $filePath, string $fileOrigName)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
        $this->filePath = $filePath;
        $this->fileOrigName = $fileOrigName;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @param \App\Services\Model\RapidUpdateService $rapidService
     */
    public function handle(RapidIngestService $rapidIngestService, RapidUpdateService $rapidService)
    {
        try {
            if (! File::exists($this->filePath)) {
                throw new Exception(t('Rapid import file does not exist while processing update job.'));
            }

            [$fileName, $filePath] = $rapidIngestService->unzipFile($this->filePath);

            $rapidIngestService->process($filePath);

            $recordsUpdated = $rapidIngestService->getUpdatedRecordsCount();
            $fields = $rapidIngestService->getUpdatedFields();

            $data = [
                'user_id'        => $this->user->id,
                'file_orig_name' => $this->fileOrigName,
                'file_name'      => $fileName,
                'fields_updated' => $fields,
                'updated_records' => $recordsUpdated
            ];

            $rapidService->create($data);

            $downloadUrl = null;
            if ($rapidIngestService->checkErrors()) {
                $downloadUrl = $rapidIngestService->createCsv();
            }

            $this->user->notify(new UpdateNotification($this->fileOrigName, $recordsUpdated, $fields, $downloadUrl));

            RapidVersionJob::dispatch($this->user);

            return;
        } catch (Exception $exception) {
            $attributes = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));
        }
    }
}
