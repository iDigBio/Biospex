<?php

namespace App\Console\Commands;

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

    }

}

