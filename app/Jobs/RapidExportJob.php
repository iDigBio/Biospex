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
class RapidExportJob implements ShouldQueue
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
    private $frmFile;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

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
            $fields = isset($this->data['exportFields']) ?
                $rapidExportService->mapExportFields($this->data) :
                $rapidExportService->mapDirectFields($this->data);

            $form = $rapidExportService->saveForm($fields, $this->user->id);
            $rapidExportService->createFileName($form, $this->user, $fields);
            $this->frmFile = $fields['frmFile'];

            $downloadUrl = $rapidExportService->buildExport($fields);

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

            $filePath = config('config.rapid_export_dir').'/'.$this->frmFile;
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
    }
}
