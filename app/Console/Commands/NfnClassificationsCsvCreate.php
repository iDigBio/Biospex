<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\NfnClassificationsCsvCreateJob;

class NfnClassificationsCsvCreate extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:csvcreate {ids?}';

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
        $ids = null ===  $this->argument('ids') ? [] : explode(',', $this->argument('ids'));
        NfnClassificationsCsvCreateJob::dispatch($ids);
    }
}
