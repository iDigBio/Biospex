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
use App\Jobs\ZooniverseReconcileJob;
use App\Jobs\ZooniverseTranscriptionJob;
use App\Services\Model\ExpeditionService;
use Illuminate\Console\Command;

class ZooniverseReconcileChainedCommand extends Command
{
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
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @return void
     */
    public function handle(ExpeditionService $expeditionService)
    {
        $expeditionIds = empty($this->argument('expeditionIds')) ?
            $this->getExpeditionIds($expeditionService) : $this->argument('expeditionIds');

        foreach ($expeditionIds as $expeditionId) {
            ZooniverseReconcileJob::withChain([
                new ZooniverseTranscriptionJob($expeditionId),
                new ZooniversePusherJob($expeditionId)
            ])->dispatch($expeditionId);
        }
    }

    /**
     * Get all expeditions for process if no ids are passed.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @return array
     */
    private function getExpeditionIds(ExpeditionService $expeditionService): array
    {
        $expeditions = $expeditionService->getExpeditionsForZooniverseProcess();

        return $expeditions->pluck('id')->toArray();
    }
}
