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
use App\Notifications\ExportNotification;
use App\Notifications\JobErrorNotification;
use App\Services\RapidExportService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param array $data
     */
    public function __construct(User $user, array $data)
    {
        $this->onQueue(config('config.default_tube'));
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidExportService $rapidExportService
     */
    public function handle(RapidExportService $rapidExportService)
    {
        try {
            $fields = $rapidExportService->mapOrderFields($this->data);

            $form = $rapidExportService->saveForm($fields, $this->user->id);

            $user = explode('@', $this->user->email);
            $fields['frmName'] = $form->present()->form_name . '_' . $user[0];

            $downloadUrl = $rapidExportService->buildExport($fields);

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
        }
    }
}
