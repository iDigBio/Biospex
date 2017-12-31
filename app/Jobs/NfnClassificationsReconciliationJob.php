<?php

namespace App\Jobs;

use File;
use App\Interfaces\Download;
use App\Interfaces\Expedition;
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
     * @var Download
     */
    public $downloadContract;

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
     * @param Expedition $expeditionContract
     * @param Download $downloadContract
     */
    public function handle(Expedition $expeditionContract, Download $downloadContract)
    {
        $this->downloadContract = $downloadContract;

        if (empty($this->ids))
        {
            $this->delete();

            return;
        }

        $ids = [];
        foreach ($this->ids as $id)
        {
            $expedition = $expeditionContract->findWith($id, ['nfnWorkflow']);

            $file = config('config.classifications_download') . '/' . $expedition->id . '.csv';

            if ( ! File::exists($file) || $expedition->nfnWorkflow === null)
            {
                continue;
            }

            $appUser = config('config.app_user');
            $csvPath = config('config.classifications_download') . '/' . $expedition->id . '.csv';
            $recPath = config('config.classifications_reconcile') . '/' . $expedition->id . '.csv';
            $tranPath = config('config.classifications_transcript') . '/' . $expedition->id . '.csv';
            $sumPath = config('config.classifications_summary') . '/' . $expedition->id . '.html';
            $pythonPath = config('config.label_reconciliations_path');
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

        $this->downloadContract->updateOrCreate($attributes, $values);
    }
}