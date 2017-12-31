<?php

namespace App\Console\Commands;

use File;
use App\Jobs\WeDigBioDashboardJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class WeDigBioDashboard extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     * ids are comma delimited expedition ids.
     *
     * @var string
     */
    protected $signature = 'wedigbio:dashboard {ids?}';

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
     * @return mixed
     */
    public function handle()
    {
        $ids = null === $this->argument('ids') ? $this->readDirectory() : explode(',', $this->argument('ids'));

        $this->dispatch((new WeDigBioDashboardJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $ids = [];
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $ids[] = basename($file, '.csv');
        }

        return $ids;
    }
}
