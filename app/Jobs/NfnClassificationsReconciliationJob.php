<?php

namespace App\Jobs;

use File;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class NfnClassificationsReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var array
     */
    public $expeditionIds;

    /**
     * @var Download
     */
    public $downloadContract;

    /**
     * NfnClassificationsCsvRequestsJob constructor.
     * @param array $expeditionIds
     */
    public function __construct(array $expeditionIds = [])
    {
        $this->expeditionIds = $expeditionIds;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle the job.
     * @param Expedition $expeditionContract
     * @param Download $downloadContract
     */
    public function handle(Expedition $expeditionContract, Download $downloadContract)
    {
        $this->downloadContract = $downloadContract;

        if (empty($this->expeditionIds))
        {
            $this->delete();

            return;
        }

        $expeditionIds = [];

        foreach ($this->expeditionIds as $expeditionId)
        {
            $expedition = $expeditionContract->findWith($expeditionId, ['nfnWorkflow']);

            $csvPath = Storage::path(config('config.nfn_downloads_classification') . '/' . $expedition->id . '.csv');
            $recPath = Storage::path(config('config.nfn_downloads_reconcile') . '/' . $expedition->id . '.csv');
            $tranPath = Storage::path(config('config.nfn_downloads_transcript') . '/' . $expedition->id . '.csv');
            $sumPath = Storage::path(config('config.nfn_downloads_summary') . '/' . $expedition->id . '.html');

            if ( ! File::exists($csvPath) || $expedition->nfnWorkflow === null)
            {
                continue;
            }

            $pythonPath = config('config.python_path');
            $reconcilePath = config('config.reconcile_path');
            $logPath = storage_path('logs/reconcile.log');
            $command = "$pythonPath $reconcilePath -w {$expedition->nfnWorkflow->panoptes_workflow_id} -r $recPath -u $tranPath -s $sumPath $csvPath &> $logPath";
            exec($command);
            $expeditionIds[] = $expedition->id;

            if (File::exists($csvPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'classification');
            }

            if (File::exists($tranPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'transcript');
            }

            if (File::exists($recPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'reconcile');
            }

            if (File::exists($sumPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'summary');
            }

        }

        NfnClassificationsTranscriptJob::dispatch($expeditionIds);
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
            'file' => $type !== 'summary' ? $expeditionId . '.csv' : $expeditionId . '.html',
            'type' => $type
        ];
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $type !== 'summary' ? $expeditionId . '.csv' : $expeditionId . '.html',
            'type' => $type
        ];

        $this->downloadContract->updateOrCreate($attributes, $values);
    }
}