<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbFix002Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:002';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs mongo command with update script.';

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
     */
    public function handle()
    {
        echo shell_exec("mongo < mongoUpdate.js");
        echo "Completed" . PHP_EOL;
    }
}
