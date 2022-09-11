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

use App\Jobs\ZooniverseReconcileJob;
use Illuminate\Console\Command;

/**
 * Class ZooniverseReconcileCommand
 *
 * @package App\Console\Commands
 */
class ZooniverseReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:reconcile {expeditionIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start reconcile process for NfnPanoptes classification.';

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
     * Start reconcile process.
     */
    public function handle()
    {
        $expeditionIds = $this->argument('expeditionIds');

        foreach ($expeditionIds as $expeditionId) {
            ZooniverseReconcileJob::dispatch($expeditionId);
        }
    }
}
