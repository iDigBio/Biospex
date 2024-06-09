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
use App\Services\Reconcile\ExpertReconcileService;
use App\Traits\SkipZooniverse;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpertReviewMigrateReconcilesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var int
     */
    public int $timeout = 1800;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        ExpertReconcileService $expertReconcileService
    )
    {
        $expedition = $expeditionRepo->findWith($this->expeditionId, ['project.group.owner']);

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [':id' => $expedition->id, ':title' => $expedition->title]));
            }

            $expertReconcileService->migrateReconcileCsv($expedition->id);

        } catch (\Throwable $throwable) {
            $attributes = [
                'subject' => t('Expert Review Migration Failed'),
                'html'    => [
                    t('Expedition %s', $expedition->title),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.')
                ],
            ];

            $expedition->project->group->owner->notify(new Generic($attributes, true));

            $this->delete();
        }
    }
}
