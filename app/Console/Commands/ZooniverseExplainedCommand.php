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

use App\Repositories\ExpeditionRepository;
use App\Services\Reconcile\ReconcileProcess;
use Illuminate\Console\Command;

class ZooniverseExplainedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:explained {expeditionIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate NfnPanoptes explained files';

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
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Reconcile\ReconcileProcess $reconcileProcessService
     */
    public function handle(ExpeditionRepository $expeditionRepo, ReconcileProcess $reconcileProcessService)
    {
        try {
            $expeditionIds = $this->argument('expeditionIds');

            foreach ($expeditionIds as $expeditionId) {
                $expedition = $expeditionRepo->findExpeditionForExpertReview($expeditionId);
                $reconcileProcessService->processExplained($expedition);
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
