<?php

namespace App\Console\Commands;

use App\Repositories\WorkflowManagerRepository;
use Illuminate\Console\Command;
use App\Interfaces\WorkflowManager;
use Queue;

class WorkFlowManagerCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'workflow:manage {expedition?}';

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
        $id = $this->argument('expedition');
        $managers = $this->workflowManagerContract->getWorkflowManagersForProcessing($id);

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
            $actor->pivot->total = $expedition->stat->subject_count;
            event('actor.pivot.queued', [$actor]);
            Queue::push('App\Services\Queue\ActorQueue', serialize($actor), $this->tube);
        });
    }
}
