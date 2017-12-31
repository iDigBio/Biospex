<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsUpdateJob;
use function foo\func;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsUpdate extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:update {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Argument is comma separated project ids.';


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
        $ids = explode(',', $this->argument('ids'));

        if (empty($ids))
        {
            return;
        }

        collect($ids)->each(function ($projectId){
            $this->dispatch((new NfnClassificationsUpdateJob($projectId))
                ->onQueue(config('config.beanstalkd.classification')));
        });
    }
}
