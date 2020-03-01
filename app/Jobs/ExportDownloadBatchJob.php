<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\NfnPanoptesExportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 36000;

    /**
     * @var string
     */
    private $downloadId;

    /**
     * ExportDownloadBatchJob constructor.
     *
     * @param string $downloadId
     */
    public function __construct(string $downloadId)
    {
        $this->onQueue(config('config.export_tube'));
        $this->downloadId = $downloadId;
    }

    /**
     * Handle download batch job.
     *
     * @param \App\Services\Actor\NfnPanoptesExportBatch $nfnPanoptesExportBatch
     */
    public function handle(NfnPanoptesExportBatch $nfnPanoptesExportBatch)
    {
        $download = $nfnPanoptesExportBatch->getDownload($this->downloadId);

        try {
            $nfnPanoptesExportBatch->process($download);
        }
        catch (\Exception $e) {
            $user = User::find(1);
            $message = [
                'Actor:' . $download->actor_id,
                'Expedition: ' . $download->expedition_id,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}