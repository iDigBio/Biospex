<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\WorkflowManager;
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
     * @var WorkflowManager
     */
    protected $manager;

    /**
     * @var
     */
    public $tube;

    /**
     * WorkFlowManagerCommand constructor.
     *
     * @param WorkflowManager $manager
     */
    public function __construct(WorkflowManager $manager)
    {
        parent::__construct();
        $this->tube = Config::get('config.beanstalkd.manager');
        $this->manager = $manager;
    }
    

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->argument('expedition');

        if ( null !== $id) {
            $managers = $this->manager->skipCache()->with(['expedition.actors'])->where(['expedition_id' => $id])->get();
        } else {
            $managers = $this->manager->skipCache()->with(['expedition.actors'])->get();
        }

        if ($managers->isEmpty()) {
            return;
        }

        $actors = $this->processWorkFlows($managers);

        foreach ($actors as $actor) {
            $actor->pivot->queued = 1;
            $actor->pivot->save();
            Queue::push('App\Services\Queue\ActorQueue', serialize($actor), $this->tube);
        }
    }

    /**
     * Process each workflow and actors
     * @param $managers
     * @return array
     */
    protected function processWorkFlows($managers)
    {
        $actors = [];
        foreach ($managers as $manager) {
            if ($manager->stopped) {
                continue;
            }

            $this->processActors($manager, $actors);
        }

        return $actors;
    }

    /**
     * Decide what actor to include in the array and being processed.
     * 
     * @param $manager
     * @param $actors
     */
    protected function processActors($manager, &$actors)
    {
        foreach ($manager->expedition->actors as $actor) {
            if ($this->checkErrorQueued($actor)) {
                return;
            }

            if ($actor->completed) {
                continue;
            }

            $actors[] = $actor;
        }
    }

    /**
     * Check if actor is in error or queued
     * @param $actor
     * @return bool
     */
    protected function checkErrorQueued($actor)
    {
        return ($actor->error || $actor->queued);
    }
}
