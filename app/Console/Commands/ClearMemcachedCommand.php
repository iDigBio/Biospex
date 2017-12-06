<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearMemcachedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:memcached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     *
     * @return mixed
     */
    public function handle()
    {
        $m = new \Memcached();
        $m->addServer('127.0.0.1', 11211);

        /* flush all items in 10 seconds */
        $m->flush(10);
    }
}
