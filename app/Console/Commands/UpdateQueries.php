<?php

namespace App\Console\Commands;

use App\Services\Model\DownloadService;
use File;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class UpdateQueries extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';
    /**
     * @var DownloadService
     */
    private $downloadService;

    /**
     * UpdateQueries constructor.
     * @param DownloadService $downloadService
     */
    public function __construct(DownloadService $downloadService)
    {
        parent::__construct();
        $this->downloadService = $downloadService;
    }

    /**
     * Fire command
     */
    public function fire()
    {
        $files = File::allFiles(config('config.classifications_download'));
        foreach ($files as $file)
        {
            $id = basename($file, '.csv');

            $csvPath = config('config.classifications_download') . '/' . $id . '.csv';
            $recPath = config('config.classifications_reconcile') . '/' . $id . '.csv';
            $tranPath = config('config.classifications_transcript') . '/' . $id . '.csv';
            $sumPath = config('config.classifications_summary') . '/' . $id . '.html';

            if (File::exists($csvPath))
            {
                $this->updateOrCreateDownloads($id, 'classifications');
            }

            if (File::exists($tranPath))
            {
                $this->updateOrCreateDownloads($id, 'transcriptions');
            }

            if (File::exists($recPath))
            {
                $this->updateOrCreateDownloads($id, 'reconciled');
            }

            if (File::exists($sumPath))
            {
                $this->updateOrCreateDownloads($id, 'summary');
            }

        }
    }

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