<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PulkitJalan\Google\Facades\Google;

class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the Job.
     */
    public function fire()
    {
        // returns instance of \Google_Service_Storage
        $fusionTables = Google::make('fusiontables');
        //$fusionTables->setScope('fusiontables');

        // list tables example
        dd($fusionTables->table->listTable());
    }
}
