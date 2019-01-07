<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
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
     * @var \App\Repositories\Interfaces\Project
     */
    protected $projectContract;

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $chartContract;

    /**
     * @var PanoptesTranscription
     */
    protected $transcription;

    /**
     * @var
     */
    protected $earliest_date;

    /**
     * @var
     */
    protected $finished_date;

    /**
     * @var mixed
     */
    protected $amChartData;

    /**
     * @var mixed
     */
    protected $amChartSeries;

    /**
     * @var mixed
     */
    protected $amChartSeriesFile;

    /**
     * AmChartNew constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\AmChart $chartContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
     */
    public function __construct(
        Project $projectContract,
        AmChart $chartContract,
        PanoptesTranscription $transcription
    )
    {
        parent::__construct();

        $this->projectContract = $projectContract;
        $this->chartContract = $chartContract;
        $this->transcription = $transcription;
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

