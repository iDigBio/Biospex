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

class NfnClassificationsReconciliationJob extends Job implements ShouldQueue
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
        $this->onQueue(config('config.beanstalkd.classification'));
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

        $chown = "chown -R www-data.www.data ";
        $chmod = "chmod -R 777 ";

        foreach ($this->expeditionIds as $expeditionId)
        {
            $expedition = $expeditionContract->findWith($expeditionId, ['nfnWorkflow']);

            $file = config('config.classifications_download') . '/' . $expedition->id . '.csv';

            if ( ! File::exists($file) || $expedition->nfnWorkflow === null)
            {
                continue;
            }
            
            $csvPath = config('config.classifications_download') . '/' . $expedition->id . '.csv';
            $recPath = config('config.classifications_reconcile') . '/' . $expedition->id . '.csv';
            $tranPath = config('config.classifications_transcript') . '/' . $expedition->id . '.csv';
            $sumPath = config('config.classifications_summary') . '/' . $expedition->id . '.html';

            exec($chown . $csvPath);
            exec($chmod . $csvPath);

            $pythonPath = config('config.label_reconciliations_path');
            $command = "python3 $pythonPath -w {$expedition->nfnWorkflow->workflow} -r $recPath -u $tranPath -s $sumPath $csvPath";
            exec($command);
            $expeditionIds[] = $expedition->id;

            if (File::exists($csvPath))
            {
                $this->updateOrCreateDownloads($expedition->id, 'classifications');
            }

            if (File::exists($tranPath))
            {
                exec($chown . $tranPath);
                exec($chmod . $tranPath);
                $this->updateOrCreateDownloads($expedition->id, 'transcriptions');
            }

            if (File::exists($recPath))
            {
                exec($chown . $recPath);
                exec($chmod . $recPath);
                $this->updateOrCreateDownloads($expedition->id, 'reconciled');
            }

            if (File::exists($sumPath))
            {
                exec($chown . $sumPath);
                exec($chmod . $sumPath);
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