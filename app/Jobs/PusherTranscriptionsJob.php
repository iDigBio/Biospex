<?php

namespace App\Jobs;

use App\Services\Model\PusherTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class PusherTranscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->data = json_decode($data);
        $this->onQueue(config('config.pusher_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\PusherTranscriptionService $service
     * @throws \Exception
     */
    public function handle(PusherTranscriptionService $service)
    {
        if ( ! isset($this->data->workflow_id))
        {
            $this->delete();
            return;
        }

        $service->processDataFromPusher($this->data);

        $this->delete();

        return;
    }
}
