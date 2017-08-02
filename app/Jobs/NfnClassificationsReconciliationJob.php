<?php

namespace App\Jobs;

use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Model\DownloadService;
use File;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsReconciliationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var array
     */
    public $ids;

    /**
     * @var DownloadService
     */
    public $downloadService;

    /**
     * NfnClassificationsCsvRequestsJob constructor.
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    /**
     * Handle the job.
     * @param ExpeditionContract $expeditionContract
     * @param DownloadService $downloadService
     */
    public function handle(ExpeditionContract $expeditionContract, DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;

        if (empty($this->ids))
        {
            $this->delete();

            return;
        }

        $ids = [];
        foreach ($this->ids as $id)
        {
            $expedition = $expeditionContract->setCacheLifetime(0)
                ->with('nfnWorkflow')
                ->find($id);

            $file = config('config.classifications_download') . '/' . $expedition->id . '.csv';

            if ( ! file_exists($file) || $expedition->nfnWorkflow === null)
            {
                continue;
            }

            $appUser = config('config.app_user');
            $csvPath = config('config.classifications_download') . '/' . $expedition->id . '.csv';
            $recPath = config('config.classifications_reconcile') . '/' . $expedition->id . '.csv';
            $tranPath = config('config.classifications_transcript') . '/' . $expedition->id . '.csv';
            $sumPath = config('config.classifications_summary') . '/' . $expedition->id . '.html';
            $pythonPath = base_path('label_reconciliations/reconcile.py');
            $command = "sudo -u $appUser python3 $pythonPath -w {$expedition->nfnWorkflow->workflow} -r $recPath -u $tranPath -s $sumPath $csvPath";
            exec($command);
            $ids[] = $expedition->id;

            if (File::exists($csvPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'classifications');
            }

            if (File::exists($tranPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'transcriptions');
            }

            if (File::exists($recPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'reconciled');
            }

            if (File::exists($sumPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'summary');
            }

        }

        $this->dispatch((new NfnClassificationsTranscriptJob($ids))->onQueue(config('config.beanstalkd.classification')));
    }

    /**
     * Update or create downloads.
     *
     * @param $expeditionId
     * @param $type
     */
    public function updateOrCreateDownloads($expeditionId, $type)
    {
        $values = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $expeditionId . '.csv',
            'type' => $type
        ];
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $expeditionId . '.csv',
            'type' => $type
        ];

        $this->downloadService->updateOrCreate($attributes, $values);
    }
}