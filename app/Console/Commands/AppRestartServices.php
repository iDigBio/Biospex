<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppRestartServices extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restart:services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to restart redis, beanstalkd, and supervisor after deploying via Envoyer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Artisan::call('route:clear');
        \Artisan::call('auth:clear-resets');
        \Artisan::call('cache:clear');
        \Artisan::call('opcache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('queue:clear');
        \Artisan::call('debugbar:clear');
        \Artisan::call('clear-compiled');
        \Artisan::call('lada-cache:flush');

        exec('sudo service memcached restart');
        exec('sudo service beanstalkd restart');
        exec('sudo service supervisor restart');
        exec('sudo service redis-server restart');
    }
}
