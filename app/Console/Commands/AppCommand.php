<?php

namespace App\Console\Commands;

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
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        /*
        EventTranscription::create($this->create());
        EventTranscription::create($this->create());

        $projectId = 13;

        $events = $this->eventContract->getEventsByProjectId($projectId);
        $data = $events->mapWithKeys(function($event) {
            $event->teams->sortBy('transcription_count');
            return [$event->id => view('frontend.events.scoreboard-content', ['event' => $event])->render()];
        });

        ScoreboardEvent::dispatch($projectId, $data->toArray());
        */
    }

    public function create() {
        return [
                'classification_id' => mt_rand(10000000, 99999999),
                'event_id' => 2,
                'team_id' => 4,
                'user_id' => 16
        ];
    }
}
