<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\WorkflowManagerContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

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
     * @var WorkflowManagerContract
     */
    protected $workflowManagerContract;

    /**
     * @var
     */
    public $tube;

    /**
     * WorkFlowManagerCommand constructor.
     *
     * @param WorkflowManagerContract $workflowManagerContract
     */
    public function __construct(WorkflowManagerContract $workflowManagerContract)
    {
        parent::__construct();
        $this->tube = Config::get('config.beanstalkd.workflow');
        $this->workflowManagerContract = $workflowManagerContract;
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->argument('expedition');

        $withRelations = ['expedition.actors', 'expedition.stat', 'expedition.nfnWorkflow'];

        $managers = null !== $id ?
            $this->workflowManagerContract->setCacheLifetime(0)
                ->findWhereWithRelations(['expedition_id', '=', $id], $withRelations) :
            $this->workflowManagerContract->setCacheLifetime(0)
                ->findAllWithRelations($withRelations);

        if ($managers->isEmpty())
        {
            return;
        }

        $this->processManagers($managers);
    }

    /**
     * Process each workflow manager and actors
     * @param  \Illuminate\Support\Collection $managers
     */
    protected function processManagers($managers)
    {
        $managers->reject(function($manager){
            return $manager->stopped;
        })->each(function($manager){
            $this->processActors($manager);
        });
    }

    /**
     * Decide what actor to include in the array and being processed.
     *
     * @param $manager
     */
    protected function processActors($manager)
    {

        $actors = $manager->expedition->actors;
        $count = $manager->expedition->stat->subject_count;

        $actors->reject(function($actor) use ($manager) {
            return $actor->pivot->error ||
                $actor->pivot->queued ||
                $actor->pivot->completed
                || $this->checkNfnWorkflow($actor, $manager);
        })->each(function($actor) use ($count){
            $actor->pivot->total = $count;
            $actor->pivot->processed = 0;
            $actor->pivot->queued = 1;
            $actor->pivot->save();
            Queue::push('App\Services\Queue\ActorQueue', serialize($actor), $this->tube);
        });
    }

    protected function checkNfnWorkflow($actor, $manager)
    {
        if ($actor->pivot->actor_id === 2)
        {
            if ( ! isset($manager->expedition->nfnWorkflow))
            {
                return true;
            }

            if ($manager->expedition->nfnWorkflow === null)
            {
                return true;
            }
        }

        return false;
    }
}
