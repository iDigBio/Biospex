<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsReconciliationJob;
use File;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsReconciliation extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     * ids are comma delimited expedition ids.
     *
     * @var string
     */
    protected $signature = 'nfn:reconcile {ids?}';

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
        $ids = null === $this->argument('ids') ? $this->readDirectory() : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsReconciliationJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $ids = [];
        $files = File::allFiles(config('config.classifications_download'));
        foreach ($files as $file)
        {
            $ids[] = basename($file, '.csv');
        }

        return $ids;
    }
}