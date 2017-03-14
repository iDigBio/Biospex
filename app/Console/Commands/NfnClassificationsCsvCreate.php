<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use App\Jobs\NfnClassificationsCsvCreateJob;

class NfnClassificationsCsvCreate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfncsv:request {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends requests to create Nfn Workflow Classifications CSV files. Argument can be comma separated ids.';

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
        $ids = null ===  $this->argument('ids') ? null : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsCsvCreateJob($ids))->onQueue(Config::get('config.beanstalkd.job')));
    }
}
