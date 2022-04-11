<?php

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
