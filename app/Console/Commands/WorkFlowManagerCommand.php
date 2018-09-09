<?php

namespace App\Console\Commands;

use App\Jobs\ActorJob;
use App\Repositories\Eloquent\WorkflowManagerRepository;
use Illuminate\Console\Command;
use App\Repositories\Interfaces\WorkflowManager;
use Queue;

class WorkFlowManagerCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'workflow:manage {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * @var WorkflowManager
     */
    protected $workflowManagerContract;

    /**
     * @var
     */
    public $tube;

    /**
     * WorkFlowManagerCommand constructor.
     *
     * @param WorkflowManager $workflowManagerContract
     */
    public function __construct(WorkflowManager $workflowManagerContract)
    {
        parent::__construct();
        $this->tube = config('config.beanstalkd.workflow');
        $this->workflowManagerContract = $workflowManagerContract;
    }


    /**
     * Execute the console command.
     *
     * @see WorkflowManagerRepository::getWorkflowManagersForProcessing() Filters out error, queued, completed.
     * @return void
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');
        $managers = $this->workflowManagerContract->getWorkflowManagersForProcessing($expeditionId);

        if ($managers->isEmpty())
        {
            return;
        }

        $managers->each(function ($manager)
        {
            $this->processActors($manager->expedition);
        });
    }

    /**
     * Decide what actor to include in the array and being processed.
     *
     * @param $expedition
     */
    protected function processActors($expedition)
    {
        $expedition->actors->each(function ($actor) use ($expedition)
        {
            $actor->pivot->total = $expedition->stat->local_subject_count;
            event('actor.pivot.queued', [$actor]);
            ActorJob::dispatch($actor);
        });
    }
}
