<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Model\PusherClassificationService;
use App\Services\Process\PusherDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class PusherDashboardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue(config('config.pusher_process_tube'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        PusherClassificationService $pusherClassificationService,
        PusherDashboardService $pusherDashboardService
    ) {
        try {

            $pusherClassificationService->getPusherClassificationModel()->chunk(25, function ($chunk) use (
                $pusherDashboardService
            ) {
                $chunk->each(function ($record) use ($pusherDashboardService) {
                    $pusherDashboardService->createDashboardRecord($record);

                    $record->delete();
                });
            });

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
