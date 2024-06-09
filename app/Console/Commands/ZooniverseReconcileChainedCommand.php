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
use App\Traits\SkipZooniverse;
use Illuminate\Console\Command;

/**
 * Class ZooniverseReconcileChainedCommand
 *
 * Runs lambda labelReconciliation for single or multiple expeditions.
 * LabelReconciliationListener will handle the reconciliation process after it's complete
 * by running ZooniverseTranscriptionJob() and ZooniversePusherJob().
 *
 */
class ZooniverseReconcileChainedCommand extends Command
{
    use SkipZooniverse;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:reconcile-chain {expeditionIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciles and process multiple expeditions including transcriptions and pusher.';

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
     * Copies classification csv to lambda-reconciliation on S3 and triggers lambda labelReconciliation function.
     * @see \App\Listeners\LabelReconciliationListener for result processing.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @return void
     */
    public function handle(ExpeditionRepository $expeditionRepo): void
    {
        $expeditionIds = empty($this->argument('expeditionIds')) ?
            $this->getExpeditionIds($expeditionRepo) : $this->argument('expeditionIds');

        foreach ($expeditionIds as $expeditionId) {
            if ($this->skipReconcile($expeditionId)) {
                continue;
            }

            $classification = config('zooniverse.directory.classification') . '/' . $expeditionId . '.csv';
            $lambda_reconciliation = config('zooniverse.directory.lambda-reconciliation') . '/' . $expeditionId . '.csv';
            \Storage::disk('s3')->copy($classification, $lambda_reconciliation);
        }
    }

    /**
     * Get all expeditions for process if no ids are passed.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @return array
     */
    private function getExpeditionIds(ExpeditionRepository $expeditionRepo): array
    {
        $expeditions = $expeditionRepo->getExpeditionsForZooniverseProcess();

        return $expeditions->pluck('id')->toArray();
    }
}
