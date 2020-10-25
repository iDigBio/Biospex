<?php

namespace App\Console\Commands;

use App\Jobs\ZooniverseReconcileJob;
use Illuminate\Console\Command;

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
    protected $description = 'Start reconcile process for Zooniverse classification.';

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
