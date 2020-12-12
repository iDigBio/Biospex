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
use App\Services\Model\RapidVersionService;
use DateHelper;
use App\Models\User;
use App\Services\CsvService;
use App\Services\Model\RapidRecordService;
use App\Services\RapidFileService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use Throwable;

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
    private $filePath;

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
     * Execute the job.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Services\Model\RapidRecordService $rapidRecordService
     * @param \App\Services\RapidFileService $rapidFileService
     * @param \App\Services\Model\RapidVersionService $rapidVersionService
     */
    public function handle(
        CsvService $csvService,
        RapidRecordService $rapidRecordService,
        RapidFileService $rapidFileService,
        RapidVersionService $rapidVersionService
    ) {
        if (! Storage::exists(config('config.rapid_version_dir'))) {
            Storage::makeDirectory(config('config.rapid_version_dir'));
        }

        $csvName = Carbon::now()->timestamp.'.csv';
        $this->filePath = config('config.rapid_version_dir') . '/' . $csvName;

        try{
            $query = $rapidRecordService->getExportQuery();
            $header = $rapidFileService->getExportHeader();
            $csvService->writerCreateFromPath(Storage::path($this->filePath));
            $csvService->insertOne($header->keys()->toArray());

            $query->chunk(1000, function ($records) use ($header, $csvService) {
                $records->each(function ($record) use ($header, $csvService) {
                    $record->updated_at = DateHelper::formatMongoDbDate($record->updated_at, 'Y-m-d H:i:s');
                    $record->created_at = DateHelper::formatMongoDbDate($record->created_at, 'Y-m-d H:i:s');

                    $record = $header->merge($record->toArray());

                    $csvService->insertOne($record->toArray());
                });
            });

            $rapidVersionService->create([
                'user_id' => $this->user->id,
                'file_name' => $csvName
            ]);

            $downloadUrl = route('admin.download.version', [base64_encode($csvName)]);
            $this->user->notify(new VersionNotification($downloadUrl));
        }
        catch (Exception $exception) {
            Storage::delete($this->filePath);
            $attributes = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Storage::delete($this->filePath);

        $attributes = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->user->notify(new JobErrorNotification($attributes));
    }
}
