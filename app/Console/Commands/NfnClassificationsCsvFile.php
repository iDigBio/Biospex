<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\NfnClassificationsCsvFileJob;

class NfnClassificationsCsvFile extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:filerequest {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends requests for Nfn Workflow Classifications CSV files. Argument can be comma separated ids.';

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
        $ids = null ===  $this->argument('ids') ? [] : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsCsvFileJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }
}