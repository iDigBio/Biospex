<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsUpdateJob;
use Illuminate\Console\Command;

class NfnClassificationsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:update {expeditionIds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Argument is comma separated expeditionIds.';


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
        $expeditionIds = explode(',', $this->argument('expeditionIds'));

        if (empty($expeditionIds))
        {
            return;
        }

        collect($expeditionIds)->each(function ($expeditionId){
            NfnClassificationsUpdateJob::dispatch($expeditionId);
        });
    }
}
