<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsFusionTableJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsFusionTable extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:fusion {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Google Fusion Table Job for NfN Classifications. Argument can be comma separated project ids or empty.';


    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ids = null === $this->argument('ids') ? [] : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsFusionTableJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }
}