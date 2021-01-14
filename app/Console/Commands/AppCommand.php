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

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Model\ExpeditionStatService;
use App\Services\Model\PanoptesProjectService;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Model\ExpeditionStatService
     */
    private $expeditionStatService;

    /**
     * @var \App\Services\Model\PanoptesProjectService
     */
    private $panoptesProjectService;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        ExpeditionStatService $expeditionStatService,
        PanoptesProjectService $panoptesProjectService
    ) {
        parent::__construct();

        $this->expeditionStatService = $expeditionStatService;
        $this->panoptesProjectService = $panoptesProjectService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $stats = $this->expeditionStatService->getBy('percent_completed', '100.00', '!=');

        echo 'project_id, expedition_id, panoptes_project_id, panoptes_workflow_id' . PHP_EOL;
        $stats->filter(function ($stat) {
            return $panoptesProject = $this->panoptesProjectService->count(['expedition_id' => $stat->expedition_id]);
        })->each(function($stat){
            $project = $this->panoptesProjectService->findBy('expedition_id', $stat->expedition_id);

            echo $project->project_id . ', ' . $project->expedition_id . ', ' . $project->panoptes_project_id . ', ' . $project->panoptes_workflow_id . PHP_EOL;
        });
    }
}