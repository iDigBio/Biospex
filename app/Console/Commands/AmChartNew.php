<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Repositories\Interfaces\AmChart;
use Illuminate\Console\Command;

/**
 * Class AmChartNew
 *
 * @package App\Console\Commands
 */
class AmChartNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:new {projectIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $chartContract;

    /**
     * AmChartNew constructor.
     *
     * @param \App\Repositories\Interfaces\AmChart $chartContract
     */
    public function __construct(
        AmChart $chartContract
    )
    {
        parent::__construct();

        $this->chartContract = $chartContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectIds = $this->argument('projectIds') === null ?
            $this->chartContract->all(['project_id'])->pluck('project_id') :
            collect(explode(',', $this->argument('projectIds')));

        $projectIds->each(function($projectId) {
            AmChartJob::dispatch($projectId);
        });
    }
}

