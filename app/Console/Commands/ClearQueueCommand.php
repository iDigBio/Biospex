<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Queue\Factory as FactoryContract;
use Symfony\Component\Console\Input\InputArgument;

class ClearQueueCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:clear {queue?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all queued jobs, by deleting all pending jobs.';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Illuminate\Contracts\Queue\Factory
     */
    private $manager;

    /**
     * @param Repository $config
     * @param \Illuminate\Contracts\Queue\Factory $manager
     */
    function __construct(Repository $config, FactoryContract $manager)
    {
        parent::__construct();
        $this->config = $config;
        $this->manager = $manager;
    }

    public function getArguments()
    {
        return array(
            array('queue', InputArgument::OPTIONAL, 'The name of the queue / pipe to clear.'),
        );
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $connection = $this->config->get('queue.default');
        $queue = $this->argument('queue') ?: $this->config->get('queue.connections.' . $connection  . '.queue');

        $this->info(sprintf('Clearing queue "%s" on "%s"', $queue, $connection));
        $cleared = $this->clear($connection, $queue);
        $this->info(sprintf('Cleared %d jobs', $cleared));
    }

    /**
     * {@inheritDoc}
     */
    public function clear($connection, $queue)
    {
        $count = 0;
        $connection = $this->manager->connection($connection);

        while ($job = $connection->pop($queue)) {
            $job->delete();
            $count++;
        }

        return $count;
    }
}
