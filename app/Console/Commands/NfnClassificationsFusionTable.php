<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\ProjectContract;
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
     * @var ProjectContract
     */
    private $projectContract;


    /**
     * Create a new command instance.
     * @param ProjectContract $projectContract
     */
    public function __construct(ProjectContract $projectContract)
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

        $projects = $this->getProjects($ids);

        $projects->each(function ($project){
            $this->dispatch((new NfnClassificationsFusionTableJob($project))
                ->onQueue(config('config.beanstalkd.classification')));
        });
    }

    /**
     * Get Projects.
     *
     * @param $ids
     * @return mixed
     */
    public function getProjects($ids)
    {
        $projects = empty($ids) ?
            $this->projectContract->setCacheLifetime(0)
                ->has('transcriptionLocations')
                ->findAll() :
            $this->projectContract->setCacheLifetime(0)
                ->has('transcriptionLocations')
                ->findWhereIn(['id', $ids]);

        return $projects;
    }
}