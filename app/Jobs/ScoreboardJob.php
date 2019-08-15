<?php

namespace App\Jobs;

use App\Events\ScoreboardEvent;
use App\Repositories\Interfaces\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScoreboardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $projectId;

    /**
     * ScoreBoardJob constructor.
     *
     * @param $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.event_tube'));
    }

    /**
     * Job handle.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @throws \Throwable
     */
    public function handle(Event $eventContract)
    {
        $events = $eventContract->getEventsByProjectId($this->projectId);
        $data = $events->mapWithKeys(function($event) {
            return [$event->id => view('common.scoreboard-content', ['event' => $event])->render()];
        });

        ScoreboardEvent::dispatch($this->projectId, $data->toArray());
    }
}
