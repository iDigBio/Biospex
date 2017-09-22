<?php

namespace App\Console\Commands;

use App\Jobs\WeDigBioDashboardJob;
use App\Services\Model\WeDigBioDashboardService;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    public $ids;

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
     */
    public function __construct(
    )
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