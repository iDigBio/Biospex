<?php
/**
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
use App\Repositories\Interfaces\RapidUpdate;
use App\Services\RapidIngestService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Storage;

class RapidUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var array
     */
    private $fileInfo;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $fields;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Update rapid records job.
     *
     * @param \App\Models\User $user
     * @param array $fileInfo
     * @param \Illuminate\Support\Collection $fields
     */
    public function __construct(User $user, array $fileInfo, Collection $fields)
    {
        $this->onQueue(config('config.default_tube'));
        $this->user = $user;
        $this->fileInfo = $fileInfo;
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @param \App\Repositories\Interfaces\RapidUpdate $rapidUpdate
     */
    public function handle(RapidIngestService $rapidIngestService, RapidUpdate $rapidUpdate)
    {
        try {
            if (! Storage::exists($this->fileInfo['filePath'])) {
                throw new Exception(t('Rapid import file does not exist while processing import job.'));
            }

            $filePath = Storage::path($this->fileInfo['filePath']);

            $rapidIngestService->process($filePath, false, $this->fields);

            $recordsUpdated = $rapidIngestService->getUpdatedRecordsCount();

            $data = [
                'user_id'        => $this->user->id,
                'file_orig_name' => $this->fileInfo['fileOrigName'],
                'file_name'      => $this->fileInfo['fileName'],
                'fields_updated' => $this->fields->toArray(),
                'updated_records' => $recordsUpdated
            ];

            $rapidUpdate->create($data);

            $downloadUrl = null;
            if ($rapidIngestService->checkErrors()) {
                $downloadUrl = $rapidIngestService->createCsv();
            }

            $this->user->notify(new UpdateNotification($this->fileInfo['fileOrigName'], $recordsUpdated, $this->fields->toArray(), $downloadUrl));

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
