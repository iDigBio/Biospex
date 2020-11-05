<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\ReconcileProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZooniverseReconcileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\ReconcileProcess $service
     * @return void
     */
    public function handle(ReconcileProcess $service)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            return;
        }

        try {
            $service->process($this->expeditionId);

            return;
        }
        catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            return;
        }
    }
}
