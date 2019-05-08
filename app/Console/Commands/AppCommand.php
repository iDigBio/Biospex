<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\TranscriptionLocation;
use App\Services\Google\FusionTableService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';


    /**
     * AppCommand constructor.
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

    }
}
