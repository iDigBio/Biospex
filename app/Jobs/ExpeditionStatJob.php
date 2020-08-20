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

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExpeditionStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * ExpeditionStatJob constructor.
     *
     * @param $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = (int) $expeditionId;
        $this->onQueue(config('config.stat_tube'));
    }

    /**
     * Execute job.
     *
     * @param \App\Repositories\Interfaces\Expedition $expedition
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     */
    public function handle(Expedition $expedition, PanoptesApiService $panoptesApiService)
    {
        $record = $expedition->findWith($this->expeditionId, ['stat', 'nfnActor']);

        $workflow = $panoptesApiService->getPanoptesWorkflow($record->panoptesProject->panoptes_workflow_id);
        $panoptesApiService->calculateTotals($workflow);
        $record->stat->local_subject_count = $expedition->getExpeditionSubjectCounts($this->expeditionId);
        $record->stat->subject_count = $panoptesApiService->getSubjectCount();
        $record->stat->transcriptions_total = $panoptesApiService->getTranscriptionsTotal();
        $record->stat->transcriptions_completed = $panoptesApiService->getTranscriptionsCompleted();
        $record->stat->percent_completed = $panoptesApiService->getPercentCompleted();

        $record->stat->save();

        if ($workflow['finished_at'] !== null) {
            event('actor.pivot.completed', $record->nfnActor);
        }
    }
}
