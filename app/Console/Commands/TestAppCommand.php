<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\ExpeditionContract;
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

    public function handle(ExpeditionContract $expeditionContract)
    {
        $expeditions = $expeditionContract->setCacheLifetime(0)
            ->getExpeditionsForNfnClassificationProcess($this->ids);
        dd($expeditions->pluck('id'));
    }

}
