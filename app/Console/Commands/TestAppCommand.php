<?php

namespace App\Console\Commands;

use App\Jobs\EventBoardJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{

    use DispatchesJobs;

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
     *
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
        $id = $this->argument('id');
        if (null === $id) {
            echo 'Project Id required' . PHP_EOL;

            return;
        }

        EventBoardJob::dispatch($id);

        return;
    }
}
