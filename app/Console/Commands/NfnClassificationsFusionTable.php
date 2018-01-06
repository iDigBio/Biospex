<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsFusionTableJob;
use App\Interfaces\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsFusionTable extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:fusion {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Google Fusion Table Job for NfN Classifications. Argument can be comma separated project ids or empty.';
    /**
     * @var Project
     */
    private $projectContract;


    /**
     * Create a new command instance.
     * @param Project $projectContract
     */
    public function __construct(Project $projectContract)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ids = null === $this->argument('ids') ? [] : explode(',', $this->argument('ids'));

        $projects = empty($ids) ?
            $this->projectContract->getProjectsHavingTranscriptionLocations() :
            $this->projectContract->getProjectsHavingTranscriptionLocations($ids);

        $projects->each(function ($project){
            NfnClassificationsFusionTableJob::dispatch($project->id);
        });
    }
}