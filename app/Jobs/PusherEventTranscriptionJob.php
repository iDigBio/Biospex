<?php

namespace App\Jobs;

use App\Services\Process\PusherEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PusherEventTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue(config('config.pusher_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\PusherEventService $service
     * @return void
     */
    public function handle(PusherEventService $service)
    {
        $service->process($this->data);

        $this->delete();

        return;

    }
}
