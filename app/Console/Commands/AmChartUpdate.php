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
    protected $signature = 'amchart:update {projectIds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the AmChart data for project pages. Argument is comma separated project projectIds.';

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
        $projectIds = explode(',', $this->argument('projectIds'));

        collect($projectIds)->each(function ($projectId){
            AmChartJob::dispatch($projectId);
        });
    }
}
