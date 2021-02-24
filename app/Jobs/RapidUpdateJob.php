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
use App\Services\Model\RapidUpdateModelService;
use App\Services\RapidIngestService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use const Grpc\STATUS_OUT_OF_RANGE;

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
    private $fileName;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * Update rapid records job.
     *
     * @param \App\Models\User $user
     * @param string $filePath
     * @param string $fileName
     */
    public function __construct(User $user, string $filePath, string $fileName)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @param \App\Services\Model\RapidUpdateModelService $rapidUpdateModelService
     */
    public function handle(RapidIngestService $rapidIngestService, RapidUpdateModelService $rapidUpdateModelService)
    {
        try {
            if (! Storage::exists($this->filePath)) {
                throw new Exception(t('Rapid update csv file does not exist while processing job.'));
            }

            $rapidHeaderRecord = $rapidIngestService->process(Storage::path($this->filePath));

            $recordsUpdated = $rapidIngestService->getUpdatedRecordsCount();

            $fields = $rapidIngestService->getUpdatedFields();

            $data = [
                'header_id'       => $rapidHeaderRecord->id,
                'user_id'         => $this->user->id,
                'file_name'       => $this->fileName,
                'fields_updated'  => $fields,
                'updated_records' => $recordsUpdated,
            ];

            $rapidUpdateModelService->create($data);

            $downloadUrl = null;
            if ($rapidIngestService->checkErrors()) {
                $downloadUrl = $rapidIngestService->createCsv();
            }

            $this->user->notify(new UpdateNotification($this->fileName, $recordsUpdated, $fields, $downloadUrl));

            RapidVersionJob::dispatch($this->user)->delay(now()->addMinutes(5));

            $this->delete();

        } catch (Exception $e) {
            $attributes = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));

            $this->delete();
        }
    }
}
