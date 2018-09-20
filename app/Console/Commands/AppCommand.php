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
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * AppCommand constructor.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(
        \App\Repositories\Interfaces\Event $eventContract,
        \App\Repositories\Interfaces\Project $projectContract
    )
    {
        parent::__construct();
        $this->eventContract = $eventContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $results = $this->projectContract->getPublicIndex();
        $results->each(function($result){
            echo $result->expeditions . PHP_EOL;
        });
        /*
        EventTranscription::create($this->create());
        EventTranscription::create($this->create());

        $projectId = 13;
        //$eventId = 1;
        //$event = $this->eventContract->getEventScoreboard($eventId, ['id']);
        //$data = $event->teams->map(function )

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
