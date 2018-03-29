<?php

namespace App\Console\Commands;

use App\Events\PollBoardEvent;
use App\Repositories\Interfaces\Event;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $eventContract)
    {
        parent::__construct();
        $this->eventContract = $eventContract;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        $events = $this->eventContract->getEventsByProjectId(53);

        $returnHTML = view('frontend.events.board', ['events' => $events])->render();

        $data = [
            'id' => 53,
            'html' => $returnHTML
        ];

        event(new PollBoardEvent($data));

    }
}
