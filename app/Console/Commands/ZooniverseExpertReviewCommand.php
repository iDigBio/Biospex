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

use App\Jobs\ExpertReviewMigrateReconcilesJob;
use App\Jobs\ExpertReviewSetProblemsJob;
use App\Services\Reconcile\ReconcileService;
use Illuminate\Console\Command;

/**
 * Class ZooniverseExpertReviewCommand
 *
 * Command to create explained for expert review.
 */
class ZooniverseExpertReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expert:review {expeditionId}';

    /**
     * The console command description.
     * @see \App\Http\Controllers\Admin\ReconcileController::create()
     *
     * @var string
     */
    protected $description = 'Runs an expedition through creating expert review migrations';

   /**
     * Execute command to create explained for expert review.
     * @see \App\Listeners\LabelReconciliationListener for result processing.
     * Will process explained and then chain:
     * @see ExpertReviewMigrateReconcilesJob
     * @see ExpertReviewSetProblemsJob
     *
     * @param \App\Services\Reconcile\ReconcileService $reconcileService
     * @return void
     */
    public function handle(ReconcileService $reconcileService): void
    {
        $expeditionId = $this->argument('expeditionId');

        $reconcileService->invokeLambdaExplained($expeditionId);
    }
}
