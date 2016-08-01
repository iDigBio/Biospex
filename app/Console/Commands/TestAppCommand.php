<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Repositories\Contracts\ExpeditionStat;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';


    /**
     * AmChartJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function fire()
    {
        $repo = app(ExpeditionStat::class);

        $stats = $repo->skipCache()->with(['expedition.project'])->get();
        foreach ($stats as $stat)
        {
            echo '---' . PHP_EOL;
            echo $stat->id . PHP_EOL;
            echo $stat->expedition->id . PHP_EOL;
            echo $stat->expedition->project->id . PHP_EOL;
        }
    }

}

