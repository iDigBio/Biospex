<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsUpdateJob;
use File;
use Illuminate\Console\Command;

class NfnClassificationsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:update {expeditionIds?} {--files=}';

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
        $expeditionIds = $this->argument('expeditionIds') === null ?
            null : explode(',', $this->argument('expeditionIds'));

        $files = $this->option('files');

        if ($expeditionIds === null && $files === null)
        {
            return;
        }

        $expeditionIds = $files !== "true" ? $expeditionIds : $this->readDirectory();

        collect($expeditionIds)->each(function ($expeditionId){
            NfnClassificationsUpdateJob::dispatch($expeditionId);
        });
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
