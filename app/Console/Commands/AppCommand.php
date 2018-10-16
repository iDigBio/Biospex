<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Project;
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
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Project $project
    )
    {
        parent::__construct();
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $projects = $this->project->all();
        $projects->each(function(){

        });
    }

}
