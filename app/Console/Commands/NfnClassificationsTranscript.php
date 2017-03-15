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
    protected $signature = 'nfnfile:transcript {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reconciled transcriptions and enter into database.';

    /**
     * @var array
     */
    private $ids = [];

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
        null === $this->argument('ids') ? $this->readDirectory() : explode(',', $this->argument('ids'));

        $this->dispatch((new NfnClassificationsTranscriptJob($this->ids))->onQueue(config('config.beanstalkd.job')));
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $this->ids[] = basename($file, '.csv');
        }
    }
}