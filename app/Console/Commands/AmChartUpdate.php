<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Repositories\Contracts\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class AmChartUpdate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:update {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the AmChart data for project pages.';
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $projects = $this->getProjects($id);

        foreach ($projects as $project)
        {
            $this->dispatch((new AmChartJob($project->id))->onQueue(Config::get('config.beanstalkd.job')));
        }
    }

    /**
     * @param $id
     * @return array
     */
    private function getProjects($id)
    {
        $repo = app(Project::class);

        if (null === $id)
        {
            $projects = $repo->skipCache()->has('expeditions.statWithStartDate')->get();
        }
        else
        {
            $projects = $repo->skipCache()->where(['id' => $id])->has('expeditions.statWithStartDate')->get();
        }

        return $projects;
    }
}
