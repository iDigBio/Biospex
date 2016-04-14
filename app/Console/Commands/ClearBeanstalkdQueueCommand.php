<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputArgument;

class ClearBeanstalkdQueueCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear a Beanstalkd tube, by deleting all pending jobs.';

    /**
     * All the queues defined for beanstalkd.
     * @var array
     */
    protected $tubes;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Defines the arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return array(
            array('tube', InputArgument::OPTIONAL, 'The name of the tube to clear.'),
        );
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->tubes = $this->argument('tube') === null ? Config::get('config.beanstalkd') : $this->argument('tube');

        is_array($this->tubes) ? $this->loopQueues() : $this->clearQueue();
    }

    /**
     * Loop through queues and remove.
     */
    protected function loopQueues()
    {
        foreach ($this->tubes as $tube) {
            $this->clearQueue($tube);
        }
    }

    /**
     * Clear Queue.
     */
    protected function clearQueue()
    {
        $this->info(sprintf('Clearing queue: %s', $this->tube));
        $pheanstalk = Queue::getPheanstalk();
        $pheanstalk->useTube($this->tube);
        $pheanstalk->watch($this->tube);

        while ($job = $pheanstalk->reserve(0)) {
            $pheanstalk->delete($job);
        }

        $this->info('...cleared.');
    }
}
