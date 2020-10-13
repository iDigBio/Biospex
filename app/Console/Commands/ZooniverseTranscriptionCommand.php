<?php

namespace App\Console\Commands;

use App\Jobs\ZooniverseTranscriptionJob;
use Illuminate\Console\Command;

class ZooniverseTranscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:transcription {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expedition reconciled transcription files for Zooniverse.';

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
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');

        ZooniverseTranscriptionJob::dispatch($expeditionId);
    }
}
