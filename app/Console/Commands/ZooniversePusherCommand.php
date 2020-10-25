<?php

namespace App\Console\Commands;

use App\Jobs\ZooniversePusherJob;
use Illuminate\Console\Command;

class ZooniversePusherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:pusher {expeditionIds?*} {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expedition transcriptions from Zooniverse and adds them to pusher data.';

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
        $expeditionIds = $this->argument('expeditionIds');
        $days = $this->option('days') === null ? null : (int) $this->option('days');

        foreach ($expeditionIds as $expeditionId) {
            ZooniversePusherJob::dispatch($expeditionId, $days);
        }
    }
}
