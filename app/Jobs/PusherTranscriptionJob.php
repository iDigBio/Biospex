<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\PusherTranscriptionRepository;
use App\Services\Transcriptions\CreatePusherTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class PusherTranscriptionJob
 *
 * @package App\Jobs
 */
class PusherTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private PusherTranscriptionRepository $pusherTranscriptionRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue(config('config.queus.pusher_process'));
    }

    /**
     * Executes moving pusher classifications to pusher transcriptions in mongodb.
     * Cron runs every 5 minutes.
     *
     * @return void
     */
    public function handle(CreatePusherTranscriptionService $createPusherTranscriptionService) {
        try {

            $createPusherTranscriptionService->process();
            $this->delete();

            return;
        } catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                'Message:'.$e->getFile().': '.$e->getLine().' - '.$e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            $this->delete();

            return;
        }
    }
}
