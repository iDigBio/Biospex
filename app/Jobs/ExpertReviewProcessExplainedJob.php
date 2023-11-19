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

use App\Jobs\Traits\SkipZooniverse;
use App\Notifications\Generic;
use App\Repositories\ExpeditionRepository;
use App\Services\Reconcile\ReconcileProcess;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpertReviewProcessExplainedJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    private int $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $expeditionId)
    {
        //
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        ReconcileProcess $reconcileProcess,
    )
    {
        $expedition = $expeditionRepo->findExpeditionForExpertReview($this->expeditionId);

        try {
            if ($this->skipReconcile($this->expeditionId)) {
                throw new \Exception(t('Expert Review for Expedition (:id) ":title" was skipped. Please contact Biospex Administration', [':id' => $expedition->id, ':title' => $expedition->title]));
            }

            $reconcileProcess->processExplained($expedition);

        } catch (\Throwable $throwable) {
            $attributes = [
                'subject' => t('Expert Review Process Explained Job Failed'),
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
