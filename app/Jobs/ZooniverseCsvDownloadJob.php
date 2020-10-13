<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZooniverseCsvDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * @var string
     */
    private $uri;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param string $uri
     */
    public function __construct(int $expeditionId, string $uri)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
        $this->uri = $uri;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @return void
     */
    public function handle(ZooniverseCsvService $service)
    {
        try {
            $service->downloadCsv($this->expeditionId, $this->uri);
        }
        catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }
    }
}
