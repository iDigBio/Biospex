<?php

namespace App\Console\Commands;

use App\Jobs\BuildAmChartData;
use App\Repositories\Contracts\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class RunAmChartData extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    /**
     * Execute the console command.
     *
     * @param Project $repo
     * @return mixed
     */
    public function handle(Project $repo)
    {
        $projects = $repo->skipCache()->has('expeditions.statWithStartDate')->get();

        foreach ($projects as $project)
        {
            $this->dispatch((new BuildAmChartData($project->id))->onQueue(Config::get('config.beanstalkd.job')));
        }
    }
}
