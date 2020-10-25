<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Model\EventTranscriptionService;
use App\Services\Model\PusherTranscriptionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ZooniversePusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * @var int|null
     */
    private $days;

    /**
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param int|null $days
     */
    public function __construct(int $expeditionId, int $days = null)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\PusherTranscriptionService $pusherTranscriptionService
     * @param \App\Services\Model\EventTranscriptionService $eventTranscriptionService
     * @return void
     */
    public function handle(PusherTranscriptionService $pusherTranscriptionService, EventTranscriptionService $eventTranscriptionService)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            return;
        }

        try
        {
            $expedition = $pusherTranscriptionService->getExpedition($this->expeditionId);

            $timestamp = isset($this->days) ? Carbon::now()->subDays($this->days) : Carbon::now()->subDays(3);

            $transcriptions = $pusherTranscriptionService->getTranscriptions($expedition->id, $timestamp);

            $transcriptions->each(function ($transcription) use ($pusherTranscriptionService, $eventTranscriptionService, $expedition) {
                $pusherTranscriptionService->processTranscripts($transcription, $expedition);
                $eventTranscriptionService->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->user_name, $transcription->classification_finished_at);
            });

            return;
        }
        catch (Exception $e)
        {
            $user = User::find(1);
            $message = [
                'Message: ' => t('An error occurred while processing pusher job for Expedition Id: %s', $this->expeditionId),
                'File: ' => $e->getFile(),
                'Line: ' => $e->getLine(),
                'Error: ' => $e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $message));

            return;
        }
    }
}
