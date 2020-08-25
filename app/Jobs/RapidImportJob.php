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
use App\Notifications\JobNotification;
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

            $filePath = Storage::path($this->path);
            $rapidIngestService->loadCsvFile($filePath);
            $rapidIngestService->setHeader();
            $rapidIngestService->storeHeader();
            $rapidIngestService->setRows();
            $rapidIngestService->processImportRows();


            if ($rapidIngestService->checkErrors()) {
                $fileUrl = $rapidIngestService->createCsv();
                $message = [
                    t('There were errors when ingesting a Rapid Records file. Click the button below to download the rows not ingested.')
                ];

                $this->sendNotice($message, $fileUrl);

                return;
            }

            $message = [t('The import for Rapid has completed.')];
            $this->sendNotice($message);

            return;

        } catch (Exception $e) {
            $message = [
                'File: '.$e->getFile(),
                'Line: '.$e->getLine(),
                'Message: '.$e->getMessage(),
            ];

            $this->sendNotice($message);
        }
    }

    /**
     * Send notice for errors.
     *
     * @param $message
     * @param null|string $fileUrl
     */
    private function sendNotice($message, $fileUrl = null)
    {
        $this->user->notify(new JobNotification($message, $fileUrl));

        $this->delete();
    }
}
