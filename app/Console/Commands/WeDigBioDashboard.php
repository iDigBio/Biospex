<?php

namespace App\Console\Commands;

use App\Jobs\PusherTranscriptionJob;
use Illuminate\Console\Command;

class WeDigBioDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add pusher classifications to dashboard';

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
        PusherTranscriptionJob::dispatch();
    }
}
