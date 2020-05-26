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

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ExpeditionStatUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Expedition Stats by setting ExpeditionStatJob. Takes comma separated expedition ids or blank.';

    /**
     * @var
     */
    private $expeditionIds;


    /**
     * Execute command
     * @param Expedition $expeditionContract
     */
    public function handle(Expedition $expeditionContract)
    {
        $this->expeditionIds = null ===  $this->argument('ids') ?
            [] :
            explode(',', $this->argument('ids'));

        $expeditions = $expeditionContract->getExpeditionStats($this->expeditionIds);

        $this->setJobs($expeditions);
    }

    /**
     * Loop stats for setting jobs.
     *
     * @param Collection $expeditions
     */
    private function setJobs($expeditions)
    {
        $projectIds = $expeditions->map(function ($expedition){
            ExpeditionStatJob::dispatch($expedition->id);

            return $expedition->project_id;
        });

        $projectIds->unique()->values()->each(function ($projectId){
            AmChartJob::dispatch($projectId);
        });
    }
}
