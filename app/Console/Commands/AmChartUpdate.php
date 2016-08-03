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
    protected $signature = 'amchart:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the AmChart data for project pages. Takes comma separated values or empty as params';

    /**
     * @var
     */
    private $projectIds;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setIds();

        $projects = $this->getProjects();

        foreach ($projects as $project)
        {
            $this->dispatch((new AmChartJob($project->id))->onQueue(Config::get('config.beanstalkd.job')));
        }
    }

    /**
     * Set project ids if passed via argument.
     */
    private function setIds()
    {
        $this->projectIds = null ===  $this->argument('ids') ? null : explode(',', $this->argument('ids'));
    }

    /**
     * Return project(s).
     *
     * @return array
     */
    private function getProjects()
    {
        $repo = app(Project::class);

        null === $this->projectIds ?
            $projects = $repo->skipCache()->has('expeditions.statWithStartDate')->get() :
            $projects = $repo->skipCache()->whereIn('id', $this->projectIds)->has('expeditions.statWithStartDate')->get();

        return $projects;
    }
}
