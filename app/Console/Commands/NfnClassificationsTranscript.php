<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsTranscriptJob;
use File;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnClassificationsTranscript extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfnfile:transcript {ids?} {--dir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reconciled transcriptions and enter into database.';

    /**
     * NfNClassificationsCsvRequests constructor.
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
        $ids = null === $this->argument('ids') ? null : explode(',', $this->argument('ids'));
        $dir = $this->option('dir');

        $this->dispatch((new NfnClassificationsTranscriptJob($ids, $dir))->onQueue(config('config.beanstalkd.job')));
    }
}