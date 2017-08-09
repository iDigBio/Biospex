<?php

namespace App\Console\Commands;

use App\Services\Csv\Csv;
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
     * TestAppCommand constructor.
     * @param Csv $csv
     */
    public function __construct(Csv $csv)
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {

    }
}
