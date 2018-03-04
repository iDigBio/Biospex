<?php

namespace App\Console\Commands;

use App\Services\Model\EventService;
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
     * @var \App\Services\Model\EventService
     */
    private $eventService;

    private $rows = [];

    /**
     * Create a new job instance.
     */
    public function __construct(EventService $eventService)
    {
        parent::__construct();
        $this->eventService = $eventService;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $event = $this->eventService->getShow(1);
        $event->groups->each(function($group){
            foreach ($group->users as $user)
            {
                $this->rows[] = [$group->title, $user->nfn_user, $user->transcriptionCount];
            }
        });
        dd($this->rows);
    }
}
