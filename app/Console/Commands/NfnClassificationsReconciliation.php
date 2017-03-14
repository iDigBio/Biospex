<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsReconciliationJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsReconciliation extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfnfile:reconcile {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reconciliation on NFN files.';

    /**
     * NfNClassificationsCsvRequests constructor.
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
        $ids = explode(',', $this->argument('ids'));
        if (null === $ids)
        {
            echo 'Ids comma separated list required.' . PHP_EOL;
            return;
        }

        $this->dispatch((new NfnClassificationsReconciliationJob($ids))->onQueue(config('config.beanstalkd.job')));
    }
}