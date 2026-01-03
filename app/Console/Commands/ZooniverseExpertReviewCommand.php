<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
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
     *
     * @see \App\Http\Controllers\Admin\ExpertReconcileController::create()
     *
     * @var string
     */
    protected $description = 'Runs an expedition through creating expert review migrations';

    /**
     * Execute command to create explained for expert review.
     *
     * @see \App\Listeners\LabelReconciliationListener for result processing.
     * Will process explained and then chain:
     * @see ExpertReviewMigrateReconcilesJob
     * @see ExpertReviewSetProblemsJob
     */
    public function handle(ReconcileService $reconcileLambdaService): void
    {
        $expeditionId = $this->argument('expeditionId');

        $reconcileLambdaService->sendToReconcileTriggerQueue($expeditionId, true);
    }
}
