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

use App\Jobs\ZooniverseClassificationCountJob;
use App\Models\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class ZooniverseClassificationCount
 */
class ZooniverseClassificationCount extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     * zooniverse:count 20 30 40
     *
     * @var string
     */
    protected $signature = 'zooniverse:count {expeditionIds?*} {--update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Classification counts';

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
        $expeditionIds = empty($this->argument('expeditionIds')) ?
            $this->getExpeditionIds() : $this->argument('expeditionIds');

        foreach ($expeditionIds as $expeditionId) {
            $expedition = Expedition::find($expeditionId);

            $this->option('update') ?
                ZooniverseClassificationCountJob::dispatch($expedition, true) :
                ZooniverseClassificationCountJob::dispatch($expedition);
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
