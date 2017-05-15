<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\ExportQueueContract;
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
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(ExportQueueContract $contract)
    {
        $result = $record = $contract->firstOrCreate(['expedition_id' => 17]);
        dd($result);
    }
}
