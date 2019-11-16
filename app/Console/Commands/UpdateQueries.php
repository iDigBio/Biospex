<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\Project;
use App\Services\Process\TranscriptionChartService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Process\TranscriptionChartService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $amChartContract;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Repositories\Interfaces\AmChart $amChartContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\Process\TranscriptionChartService $service
     */
    public function __construct(AmChart $amChartContract, Project $projectContract, TranscriptionChartService $service)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
        $this->service = $service;
        $this->amChartContract = $amChartContract;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $projectIds = $this->amChartContract->all(['project_id'])->pluck('project_id');
        $projectIds->each(function($id) {
            $project = $this->projectContract->getProjectForAmChartJob($id);
            $project->amChart->series = [];
            $project->amChart->data = [];
            $project->amChart->save();
            $this->service->process($project);
            echo 'processed project ' . $id . PHP_EOL;
        });
    }

}