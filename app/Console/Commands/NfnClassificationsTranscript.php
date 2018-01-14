<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsTranscriptJob;
use File;
use Illuminate\Console\Command;

class NfnClassificationsTranscript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:transcript {expeditionIds?}';

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
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));

        NfnClassificationsTranscriptJob::dispatch($expeditionIds);
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}