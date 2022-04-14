<?php

namespace App\Console\Commands;

use App\Jobs\EncodeTranscriptionsJob;
use App\Jobs\EncodeTranscriptionsUpdateJob;
use Illuminate\Console\Command;

class EncodeTranscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encode:transcriptions {--sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to encode transcription and reconcile columns';

    /**
     * AppCommand constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->onConnection('long-beanstalkd')->onQueue(config('config.working_tube'));

    }

    /**
     * @throws \Exception
     * _id, updated_at, created_at
     */
    public function handle()
    {
        if ($this->option('sync')) {
            EncodeTranscriptionsJob::dispatch();

            return;
        }

        EncodeTranscriptionsUpdateJob::dispatch();
    }
}
