<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Support\Facades\Config;

class WorkFlowManagerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * Class constructor
     *
     * @param WorkflowManager $manager
     */
    public function __construct(WorkflowManager $manager)
    {
        $this->manager = $manager;
        $this->queue = Config::get('config.beanstalkd.workflow');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $managers = $this->manager->allWith(['expedition.actors']);

        if (empty($managers)) {
            return;
        }

        foreach ($managers as $manager) {
            if ($this->checkProcess($manager)) {
                continue;
            }

            Queue::push('App\Services\Queue\QueueFactory', ['id' => $manager->id, 'class' => 'WorkflowManagerQueue'], $this->queue);

            $manager->queue = 1;
            $manager->save();
        }
    }

    /**
     * @param $manager
     * @return bool
     */
    public function checkProcess($manager)
    {
        if ($manager->stopped == 1 || $manager->error == 1 || $manager->queue == 1) {
            return true;
        }

        return false;
    }
}
