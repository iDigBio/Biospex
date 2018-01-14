<?php

namespace App\Console\Commands;

use File;
use App\Jobs\WeDigBioDashboardJob;
use Illuminate\Console\Command;

class WeDigBioDashboard extends Command
{
    /**
     * The name and signature of the console command.
     * expeditionIds are comma delimited expedition expeditionIds.
     *
     * @var string
     */
    protected $signature = 'wedigbio:dashboard {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run WeDigBio dashboard to create/update records';

    /**
     * WeDigBioDashboard constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));

        WeDigBioDashboardJob::dispatch($expeditionIds);
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}
