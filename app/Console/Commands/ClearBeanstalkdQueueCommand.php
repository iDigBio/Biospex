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
        $this->tubes = Config::get('config.beanstalkd');

        $tubes = ($this->argument('tube')) ? $this->argument('tube') : $this->tubes;

        is_array($tubes) ? $this->loopQueues($tubes) : $this->clearQueue($tubes);

        return;
    }

    /**
     * Loop through queues and remove.
     *
     * @param $tubes
     */
    protected function loopQueues($tubes)
    {
        foreach ($tubes as $tube) {
            $this->clearQueue($tube);
        }

        return;
    }

    /**
     * Clear Queue.
     *
     * @param $queue
     */
    protected function clearQueue($tube)
    {
        $this->info(sprintf('Clearing queue: %s', $tube));
        $pheanstalk = Queue::getPheanstalk();
        $pheanstalk->useTube($tube);
        $pheanstalk->watch($tube);

        while ($job = $pheanstalk->reserve(0)) {
            $pheanstalk->delete($job);
        }

        $this->info('...cleared.');

        return;
    }
}
