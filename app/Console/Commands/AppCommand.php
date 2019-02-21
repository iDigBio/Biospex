<?php

namespace App\Console\Commands;

use App\Facades\CountHelper;
use App\Models\EventTeam;
use App\Repositories\Interfaces\PanoptesTranscription;
use Illuminate\Console\Command;

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
     * @var \App\Console\Commands\PanoptesTranscription
     */
    private $transcriptionContract;

    /**
     * Create a new job instance.
     */
    public function __construct(PanoptesTranscription $transcriptionContract)
    {
        parent::__construct();
        $this->transcriptionContract = $transcriptionContract;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $result = EventTeam::where('event_id', 1)->first();
        dd($result->uuid);
    }
}
