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

namespace App\Console\Commands;

use App\Jobs\Traits\SkipNfn;
use App\Repositories\Interfaces\ExpeditionStat;
use App\Repositories\Interfaces\PanoptesProject;
use App\Services\Api\PanoptesApiService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs, SkipNfn;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Repositories\Interfaces\PanoptesProject
     */
    private $panoptesProject;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $panoptesApiService;

    /**
     * @var \App\Repositories\Interfaces\ExpeditionStat
     */
    private $expeditionStat;

    /**
     * UpdateQueries constructor.
     *
     */
    public function __construct(
        PanoptesProject $panoptesProject,
        PanoptesApiService $panoptesApiService,
        ExpeditionStat $expeditionStat
    )
    {
        parent::__construct();
        $this->panoptesProject = $panoptesProject;
        $this->panoptesApiService = $panoptesApiService;
        $this->expeditionStat = $expeditionStat;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $records = $this->panoptesProject->all();
        $records->filter(function($record){
            return isset($record->expedition_id);
        })->mapWithKeys(function($record){
            return [$record->expedition_id => $record->panoptes_workflow_id];
        })->each(function($workflowId, $expeditionId){
            $workflow = $this->panoptesApiService->getPanoptesWorkflow($workflowId);
            $this->panoptesApiService->calculateTotals($workflow);

            $stat = $this->expeditionStat->findBy('expedition_id', $expeditionId);
            $stat->subject_count = $this->panoptesApiService->getSubjectCount();
            $stat->transcriptions_total = $this->panoptesApiService->getTranscriptionsTotal();
            $stat->transcriptions_completed = $this->panoptesApiService->getTranscriptionsCompleted();
            $stat->percent_completed = $this->panoptesApiService->getPercentCompleted();

            $stat->save();
        });

    }

}