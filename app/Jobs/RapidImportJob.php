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
use App\Notifications\ImportNotification;
use App\Notifications\JobErrorNotification;
use App\Services\RapidIngestService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

class RapidImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var string
     */
    private $path;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param string $path
     */
    public function __construct(User $user, string $path)
    {
        $this->onQueue(config('config.default_tube'));
        $this->user = $user;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     */
    public function handle(RapidIngestService $rapidIngestService)
    {
        try {
            if (! Storage::exists($this->path)) {
                throw new Exception(t('Rapid import file does not exist while processing import job.'));
            }

            [$csvFilePath, $fileName] = $rapidIngestService->unzipFile($this->path);

            if (!isset($csvFilePath)) {
                throw new Exception(t('CSV file could not be extracted from zip file.'));
            }

            $rapidIngestService->loadCsvFile($csvFilePath);
            $rapidIngestService->setHeader();
            $rapidIngestService->storeHeader();
            $rapidIngestService->setRows();
            $rapidIngestService->processImportRows();

            $downloadUrl = null;
            if ($rapidIngestService->checkErrors()) {
                $downloadUrl = $rapidIngestService->createCsv();
            }

            $this->user->notify(new ImportNotification($downloadUrl));

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
