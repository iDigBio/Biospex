<?php

namespace App\Console\Commands;

use App\Services\Model\ExpeditionService;
use App\Services\Process\ReconcileProcess;
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
    protected $description = 'Generate Zooniverse explained files';

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
     * @param \App\Services\Process\ReconcileProcess $reconcileProcessService
     */
    public function handle(ExpeditionService $expeditionService, ReconcileProcess $reconcileProcessService)
    {
        try {
            $expeditionIds = $this->argument('expeditionIds');

            foreach ($expeditionIds as $expeditionId) {
                $expedition = $expeditionService->findExpeditionForExpertReview($expeditionId);
                $reconcileProcessService->processExplained($expedition);
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
