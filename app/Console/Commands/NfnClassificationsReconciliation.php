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
     * Ids are comma delimited expedition expeditionIds.
     *
     * @var string
     */
    protected $signature = 'nfn:reconcile {expeditionIds?}';

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
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));

        NfnClassificationsReconciliationJob::dispatch($expeditionIds);
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::files(config('config.classifications_download'));
        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}