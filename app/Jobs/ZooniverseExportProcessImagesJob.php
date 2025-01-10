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

use App\Models\ExportQueue;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\ActorDirectory;
use App\Services\Actor\Zooniverse\ZooniverseExportProcessImages;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ZooniverseExportProcessImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected ExportQueue $exportQueue, protected ActorDirectory $actorDirectory)
    {
        $this->exportQueue = $exportQueue->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job.
     */
    public function handle(ZooniverseExportProcessImages $zooniverseExportProcessImages): void
    {
        // Make sure it's always stage 1 entering this job.
        $this->exportQueue->load('expedition');
        $this->exportQueue->stage = 1;
        $this->exportQueue->save();
        Artisan::call('export:poll');

        $zooniverseExportProcessImages->process($this->exportQueue, $this->actorDirectory);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $this->exportQueue->error = 1;
        $this->exportQueue->save();

        $attributes = [
            'subject' => t('Expedition Export Process Error'),
            'html' => [
                t('Queue Id: %s', $this->exportQueue->id),
                t('Expedition Id: %s', $this->exportQueue->expedition->id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];
        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
