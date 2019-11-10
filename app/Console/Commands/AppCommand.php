<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Project;
use App\Services\Process\TranscriptionChartService;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Process\TranscriptionChartService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        Project $projectContract,
        TranscriptionChartService $service
    )
    {
        parent::__construct();
        $this->service = $service;
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the job.  project 16 workflow ids 2343, 2504, 5090, 6556
     */
    public function handle()
    {
        $project = $this->projectContract->getProjectForAmChartJob(13);
        $this->service->process($project);
    }

}