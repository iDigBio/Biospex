<?php

namespace App\Console\Commands;

use App\Jobs\NotificationsJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class NotificationsUpdateCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run notifications to send any new messages to the user.';

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
     * @return mixed
     */
    public function handle()
    {
        $this->dispatch((new NotificationsJob())->onQueue(Config::get('config.beanstalkd.job')));
    }
}
