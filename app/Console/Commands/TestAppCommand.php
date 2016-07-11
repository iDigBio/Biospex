<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Console\Command;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

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
     * BuildAmChartData constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
    }

}
