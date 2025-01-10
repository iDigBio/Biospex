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

use App\Models\Download;
use App\Notifications\Generic;
use App\Services\Actor\Zooniverse\ZooniverseExportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Class ExportDownloadBatchJob
 */
class ExportDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * ExportDownloadBatchJob constructor.
     */
    public function __construct(protected Download $download)
    {
        $this->download = $this->download->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Handle download batch job.
     *
     * @throws \Exception
     */
    public function handle(ZooniverseExportBatch $zooniverseExportBatch): void
    {
        $this->download->load(['expedition.project.group.owner', 'actor']);

        $zooniverseExportBatch->process($this->download);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Export Download Batch Error'),
            'html' => [
                t('The batch export for Expedition %s has failed.', $this->download->expedition->title),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];

        $this->download->expedition->project->group->owner->notify(new Generic($attributes, true));
    }
}
