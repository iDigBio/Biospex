<?php

namespace App\Console\Commands;

use App\Jobs\UpdateExpeditionStat;
use App\Repositories\Contracts\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

class RunUpdateExpeditionStat extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:run {project?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Project $repo
     * @return mixed
     */
    public function handle(Project $repo)
    {
        $projectId = $this->argument('project');

        if (null === $projectId)
        {
            $projects = $repo->skipCache()->has('expeditions.stat')->get();
            foreach ($projects as $project)
            {
                $record = $repo->with(['expeditions'])->find($project->id);
                $this->processExpeditions($record->id, $record->expeditions);
            }
        }
        else
        {
            $project = $repo->skipCache()->has('expeditions.stat')->find($projectId);

            if ($project->isEmpty())
            {
                return;
            }

            $record = $repo->with(['expeditions'])->find($project->id);
            $this->processExpeditions($record->id, $record->expeditions);
        }

    }

    /**
     * Loop through expeditions. 
     * 
     * @param $projectId
     * @param $expeditions
     */
    protected function processExpeditions($projectId, $expeditions)
    {
        foreach ($expeditions as $expedition)
        {
            $this->setJob($projectId, $expedition->id);
        }
    }

    /**
     * Create job for the expedition.
     * 
     * @param $projectId
     * @param $expeditionId
     */
    protected function setJob($projectId, $expeditionId)
    {
        Log::alert('Dispatching ' . $projectId . ' ' . $expeditionId);
        $this->dispatch((new UpdateExpeditionStat($projectId, $expeditionId))->onQueue('job'));
    }
}
