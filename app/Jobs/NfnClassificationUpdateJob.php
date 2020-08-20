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

use App\Repositories\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationUpdateJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * NfnClassificationUpdateJob constructor.
     * @param int $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute job.
     *
     * @param Expedition $expeditionContract
     */
    public function handle(Expedition $expeditionContract)
    {
        $expedition = $expeditionContract->getExpeditionsHavingPanoptesProjects($this->expeditionId);

        if ($this->checkIfExpeditionShouldProcess($expedition))
        {
            $this->delete();

            return;
        }

        AmChartJob::dispatch($expedition->project_id);

        $this->delete();
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkIfExpeditionShouldProcess($expedition)
    {
        return null === $expedition
            || ! isset($expedition->panoptesProject)
            || null === $expedition->panoptesProject->panoptes_workflow_id
            || null === $expedition->panoptesProject->panoptes_project_id
            || null === $expedition->nfnActor;
    }
}
