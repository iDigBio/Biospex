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
        $this->tube = Config::get('config.beanstalkd.workflow');
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
            $managers = $this->manager->skipCache()->with(['expedition.actors', 'expedition.stat'])->where(['expedition_id' => $id])->get();
        } else {
            $managers = $this->manager->skipCache()->with(['expedition.actors', 'expedition.stat'])->get();
        }

        if ($managers->isEmpty()) {
            return;
        }

        $this->processManagers($managers);
    }

    /**
     * Process each workflow manager and actors
     * @param array $managers
     */
    protected function processManagers($managers)
    {
        foreach ($managers as $manager) {
            if ($manager->stopped) {
                continue;
            }

            $this->processActors($manager->expedition->actors, $manager->expedition->stat->subject_count);
        }
    }

    /**
     * Decide what actor to include in the array and being processed.
     * 
     * @param array $actors
     * @param int $count
     */
    protected function processActors($actors, $count)
    {
        foreach ($actors as $actor) {
            if ($this->checkErrorQueued($actor)) {
                continue;
            }

            if ($actor->completed) {
                continue;
            }

            $actor->pivot->total = $count;
            $actor->pivot->processed = 0;
            $actor->pivot->queued = 1;
            $actor->pivot->save();
            Queue::push('App\Services\Queue\ActorQueue', serialize($actor), $this->tube);
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
