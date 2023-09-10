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

use App\Jobs\ZooniverseCsvJob;
use App\Models\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class ZooniverseClassificationCount
 *
 * @package App\Console\Commands
 */
class ZooniverseCsvCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     * Ids passed 1 2 3 4
     * Passing --no-delay means true.
     *
     * @var string
     */
    protected $signature = 'zooniverse:csv {expeditionIds*} {--no-delay}';

    /**
     * The console command description.
     * This is used to generate new CSVs without running through the whole CSV job process, including delays.
     * Can move right to ZooniverseDownloadCommand after csv is generated.
     *
     * @var string
     */
    protected $description = 'Calls ZooniverseCsvJob with Expedition ids. If --no-delay true, do not pass on to ZooniverseProcessCsvJob.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Process command.
     */
    public function handle()
    {
        $expeditionIds = $this->argument('expeditionIds');
        $noDelay = $this->option('no-delay');

        foreach ($expeditionIds as $expeditionId) {
            ZooniverseCsvJob::dispatch((int) $expeditionId, $noDelay);
        }
    }

    /**
     * Get expedition ids having panoptes project.
     *
     * @return mixed
     */
    private function getExpeditionIds()
    {
        return Expedition::whereHas('panoptesProject')->get('id')->pluck('id')->toArray();
    }
}
