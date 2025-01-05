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

use App\Jobs\ZooniversePusherJob;
use App\Models\Expedition;
use Illuminate\Console\Command;

/**
 * Class ZooniversePusherCommand
 */
class ZooniversePusherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:pusher {expeditionIds?*} {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expedition transcriptions from Zooniverse and adds them to pusher data.';

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
     * Execute the console command.
     */
    public function handle()
    {
        $expeditionIds = $this->argument('expeditionIds');
        $days = $this->option('days') === null ? null : (int) $this->option('days');

        foreach ($expeditionIds as $expeditionId) {
            $expedition = Expedition::find($expeditionId);
            ZooniversePusherJob::dispatch($expedition, $days);
        }
    }
}
