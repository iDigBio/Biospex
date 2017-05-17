<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class ClearBeanstalkdQueueCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:clear {tube?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear a Beanstalkd tube by deleting all pending jobs.';

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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->tubes = $this->argument('tube') === null ? Config::get('config.beanstalkd') : explode(',', $this->argument('tube'));

        collect($this->tubes)->each(function ($tube){
            $this->clearQueue($tube);
        });

        DB::statement("UPDATE actor_expedition set queued = 0;");
    }

    /**
     * Clear beanstalk tube.
     *
     * @param $tube
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
    }
}
