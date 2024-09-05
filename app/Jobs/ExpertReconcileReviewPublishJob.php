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

use App\Notifications\Generic;
use App\Repositories\ExpeditionRepository;
use App\Services\Reconcile\ExpertReconcilePublishService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\CannotInsertRecord;

/**
 * Class ExpertReconcileReviewPublishJob
 *
 * @package App\Jobs
 */
class ExpertReconcileReviewPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * ExpertReconcileReviewPublishJob constructor.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Reconcile\ExpertReconcilePublishService $expertReconcilePublishService
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     */
    public function handle(
        ExpertReconcilePublishService $expertReconcilePublishService,
        ExpeditionRepository $expeditionRepository
    ): void {
        $expedition = $expeditionRepository->findWith($this->expeditionId, ['project.group.owner']);

        try {
            $expertReconcilePublishService->publishReconciled($this->expeditionId);
        } catch (CannotInsertRecord|Exception $e) {
            $attributes = [
                'subject' => t('Expert Reconcile Publish Error'),
                'html'    => [
                    t('An error occurred while importing the Darwin Core Archive.'),
                    t('File: %s', $e->getFile()),
                    t('Line: %s', $e->getLine()),
                    t('Message: %s', $e->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];
            $expedition->project->group->owner->notify(new Generic($attributes, true));
        }

        $this->delete();
    }
}
