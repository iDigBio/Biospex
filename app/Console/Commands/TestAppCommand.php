<?php

namespace App\Console\Commands;

use App\Services\Actor\ActorImageService;
use App\Services\Image\ImagickService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Imagick;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    public $projectId;
    public $expeditionId;
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
