<?php

namespace App\Jobs;

use App\Events\PollBoardEvent;
use App\Repositories\Interfaces\Event;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\AmChart;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EventBoardJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $projectId;

    /**
     * EventBoardJob constructor.
     *
     * @param $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.beanstalkd.event'));
    }

    /**
     * Job handle.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @throws \Throwable
     */
    public function handle(
        Event $eventContract
    )
    {
        $events = $eventContract->getEventsByProjectId($this->projectId);

        $returnHTML = view('frontend.events.board', ['events' => $events])->render();

        $data = [
            'id' => $this->projectId,
            'html' => $returnHTML
        ];

        event(new PollBoardEvent($data));
    }
}
