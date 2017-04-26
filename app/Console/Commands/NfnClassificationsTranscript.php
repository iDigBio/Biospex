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
    protected $signature = 'nfn:transcript {ids?}';

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
        $ids = null === $this->argument('ids') ? $this->readDirectory() : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsTranscriptJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $ids = [];
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $ids[] = basename($file, '.csv');
        }

        return $ids;
    }
}