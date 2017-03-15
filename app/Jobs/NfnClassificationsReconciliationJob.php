<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionContract;
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
     * @var bool
     */
    public $dir;


    /**
     * NfnClassificationsCsvRequestsJob constructor.
     * @param array $ids
     * @param bool $dir
     */
    public function __construct(array $ids = [], $dir = false)
    {
        $this->ids = $ids;
        $this->dir = $dir;
    }

    /**
     * Handle the job.
     * @param ExpeditionContract $expeditionContract
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        if ($this->dir)
        {
            $this->readDirectory();
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

        exec('sudo chown -R www-data.www-data ' . config('config.classifications_dir'));

        $this->dispatch((new NfnClassificationsTranscriptJob($this->ids))->onQueue(config('config.beanstalkd.job')));
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $files = File::allFiles(config('config.classifications_download'));
        foreach ($files as $file)
        {
            $this->ids[] = basename($file, '.csv');
        }
    }
}