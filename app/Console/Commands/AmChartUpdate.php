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

use App\Jobs\AmChartJob;
use App\Models\AmChart;
use Illuminate\Console\Command;

/**
 * Class AmChartUpdate
 *
 * @package App\Console\Commands
 */
class AmChartUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:update {projectIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update AmChart data for projects.';

    /**
     * AmChartUpdate constructor.
     *
     * @param \App\Models\AmChart $amChart
     */
    public function __construct(private readonly AmChart $amChart)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $projectIds = empty($this->argument('projectIds')) ?
            $this->amChart->all(['project_id'])->pluck('project_id') : collect($this->argument('projectIds'));

        $projectIds->each(function($projectId) {
            AmChartJob::dispatch((int) $projectId);
        });
    }
}
