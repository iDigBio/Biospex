<?php

namespace App\Console\Commands;

use App\Jobs\AmChartImageJob;
use App\Repositories\Interfaces\AmChart;
use Illuminate\Console\Command;

class AmChartImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:image {projectIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $amChartContract;

    /**
     * Create a new command instance.
     *
     * @param \App\Repositories\Interfaces\AmChart $amChartContract
     */
    public function __construct(AmChart $amChartContract)
    {
        parent::__construct();
        $this->amChartContract = $amChartContract;
    }

    /**
     * Execute command.
     */
    public function handle()
    {
        $amCharts = $this->argument('projectIds') === null ?
            $this->amChartContract->all() :
            $this->amChartContract->whereIn('project_id', explode(',', $this->argument('projectIds')), ['project_id']);

        $amCharts->each(function($amChart) {
            AmChartImageJob::dispatch($amChart);
        });
    }
}
