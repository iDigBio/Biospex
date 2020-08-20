<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationCountJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class NfnClassificationCount extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:count {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Classification counts';

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
     * Process command.
     */
    public function handle()
    {
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));

        foreach ($expeditionIds as $expeditionId) {
            NfnClassificationCountJob::dispatch((int) $expeditionId);
        }
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::files(Storage::path(config('config.nfn_downloads_classification')));
        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}
