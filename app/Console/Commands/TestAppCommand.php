<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

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

        $lngArray = \Lang::get('buttons');
        foreach ($lngArray as $key => $value)
        {
            $result = \App\Models\Translation::where('group', 'buttons')->where('key', $key)->first();
            if ($result === null) {
                echo $key . PHP_EOL;
            }
        }


    }
}
