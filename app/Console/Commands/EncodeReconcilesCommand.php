<?php

namespace App\Console\Commands;

use App\Jobs\EncodeReconcilesJob;
use App\Jobs\EncodeReconcilesUpdateJob;
use Illuminate\Console\Command;

class EncodeReconcilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encode:reconciles {--sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start job for encoding reconcile columns.';

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
     */
    public function handle()
    {
        if ($this->option('sync')) {
            EncodeReconcilesJob::dispatch();

            return;
        }

        EncodeReconcilesUpdateJob::dispatch();
    }
}
