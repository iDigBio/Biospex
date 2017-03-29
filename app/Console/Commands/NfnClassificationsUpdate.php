<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class NfnClassificationsUpdate extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Argument can be comma separated ids or empty.';


    /**
     * Create a new command instance.
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
        $ids = null === $this->argument('ids') ? [] : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsUpdateJob($ids))->onQueue(Config::get('config.beanstalkd.job')));
    }
}
