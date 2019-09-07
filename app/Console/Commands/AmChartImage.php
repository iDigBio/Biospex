<?php

namespace App\Console\Commands;

use App\Jobs\AmChartImageJob;
use App\Repositories\Interfaces\Project;
use Illuminate\Console\Command;

class AmChartImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:image {projectId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Project $projectContract)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projectId = $this->argument('projectId');
        $project = $this->projectContract->find($projectId);

        AmChartImageJob::dispatch($project);
    }
}
