<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use Illuminate\Console\Command;

class AmChartUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:update {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the AmChart data for project pages. Argument is comma separated project ids.';

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
        $ids = explode(',', $this->argument('ids'));

        collect($ids)->each(function ($projectId){
            AmChartJob::dispatch($projectId);
        });
    }
}
