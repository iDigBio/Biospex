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

use App\Models\Expedition;
use App\Notifications\Generic;
use App\Services\Reconcile\ExpertReconcilePublishService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ExpertReconcileReviewPublishJob
 */
class ExpertReconcileReviewPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ExpertReconcileReviewPublishJob constructor.
     */
    public function __construct(protected Expedition $expedition)
    {
        $this->expedition = $expedition->withoutRelations();
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Handle Job.
     *
     * @throws \Exception
     */
    public function handle(ExpertReconcilePublishService $expertReconcilePublishService): void
    {
        $this->expedition->load(['project.group.owner']);

        $expertReconcilePublishService->publishReconciled($this->expedition);

        $this->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $attributes = [
            'subject' => t('Expert Reconcile Publish Error'),
            'html' => [
                t('An error occurred while importing the Darwin Core Archive.'),
                t('File: %s', $exception->getFile()),
                t('Line: %s', $exception->getLine()),
                t('Message: %s', $exception->getMessage()),
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];
        $this->expedition->project->group->owner->notify(new Generic($attributes, true));
    }
}
