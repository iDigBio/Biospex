<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionContract;
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
     * NfnClassificationsCsvRequestsJob constructor.
     * @param $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    /**
     * Handle the job.
     * @param ExpeditionContract $expeditionContract
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        if (null === $this->ids)
        {
            $this->delete();

            return;
        }

        foreach ($this->ids as $id)
        {
            $expedition = $expeditionContract->setCacheLifetime(0)
                ->expeditionFindWith($id, 'nfnWorkflow');

            if ( ! file_exists(config('config.classifications_download') . '/' . $expedition->id . '.csv'))
            {
                continue;
            }

            $csvPath = config('config.classifications_download') . '/' . $expedition->id . '.csv';
            $recPath = config('config.classifications_reconcile') . '/' . $expedition->id . '.csv';
            $tranPath = config('config.classifications_transcript') . '/' . $expedition->id . '.csv';
            $sumPath = config('config.classifications_summary') . '/' . $expedition->id . '.html';
            $pythonPath = base_path('label_reconciliations/reconcile.py');
            $command = "sudo python3 $pythonPath -w {$expedition->nfnWorkflow->workflow} -r $recPath -u $tranPath -s $sumPath $csvPath";
            exec($command);
        }

        $this->dispatch((new NfnClassificationsTranscriptJob($this->ids))->onQueue(config('config.beanstalkd.job')));
    }
}