<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputArgument;

class WorkFlowManagerCommand extends Command
{
    public $tube;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'workflow:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * @var WorkflowManagerInterface
     */
    protected $workflow;

    /**
     * WorkFlowManagerCommand constructor.
     *
     * @param WorkflowManager $workflow
     */
    public function __construct(WorkflowManager $workflow)
    {
        parent::__construct();
        $this->tube = Config::get('config.beanstalkd.workflow');
        $this->workflow = $workflow;
    }

    /**
     * Defines the arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['expedition', InputArgument::OPTIONAL, 'The id of an Expedition to process.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->argument('expedition');

        if ( ! empty($id)) {
            $workFlows = $this->workflow->findByExpeditionIdWith($id, ['expedition.actors']);
        } else {
            $workFlows = $this->workflow->allWith(['expedition.actors']);
        }


        if ($workFlows->isEmpty()) {
            return;
        }

        $actors = $this->processWorkFlows($workFlows);

        foreach ($actors as $actor) {
            $actor->pivot->queued = 1;
            $actor->pivot->save();
            Queue::push('App\Services\Queue\ActorQueue', serialize($actor), $this->tube);
        }
    }

    /**
     * Process each workflow and actors
     * @param $workFlows
     * @return array
     */
    protected function processWorkFlows($workFlows)
    {
        $actors = [];
        foreach ($workFlows as $workFlow) {
            if ($workFlow->stopped) {
                continue;
            }

            $this->processActors($workFlow, $actors);
        }

        return $actors;
    }

    /**
     * Decide what actor to include in the array and being processed
     * @param $workFlow
     * @param $actors
     */
    protected function processActors($workFlow, &$actors)
    {
        foreach ($workFlow->expedition->actors as $actor) {
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
