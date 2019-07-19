<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\NfnWorkflow;
use Illuminate\Console\Command;
use Storage;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * AppCommand constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $transcriptDir = config('config.nfn_downloads_transcript');

        echo Storage::exists($transcriptDir . '/69.csv') . PHP_EOL;
        $csvFile = Storage::path($transcriptDir . '/69.csv');
        echo Storage::exists($csvFile);
        echo $csvFile . PHP_EOL;
    }
}