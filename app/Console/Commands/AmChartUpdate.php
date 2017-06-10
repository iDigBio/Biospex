<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AmChartUpdate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the AmChart data for project pages. Takes comma separated values or empty as params';

    /**
     * Create a new command instance.
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
        $ids = null ===  $this->argument('ids') ? [] : explode(',', $this->argument('ids'));

        $this->dispatch((new AmChartJob($ids))->onQueue(config('config.beanstalkd.chart')));
    }
}
