<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppTestCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {id?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
    }
}
