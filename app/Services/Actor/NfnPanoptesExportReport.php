<?php

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Facades\GeneralHelper;
use App\Notifications\NfnExportComplete;

use App\Repositories\Interfaces\ExportQueue;
use App\Repositories\Interfaces\ExportQueueFile;
use File;

/**
 * Class NfnPanoptesExportReport
 *
 * @see \App\Services\Actor\NfnPanoptes::processQueue()
 * @package App\Services\Actor
 */
class NfnPanoptesExportReport extends NfnPanoptesBase
{
    /**
     * @var \App\Repositories\Interfaces\ExportQueue
     */
    private $exportQueueContract;

    /**
     * @var \App\Repositories\Interfaces\ExportQueueFile
     */
    private $exportQueueFileContract;

    /**
     * NfnPanoptesExportReport constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueue $exportQueueContract
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFileContract
     */
    public function __construct(
        ExportQueue $exportQueueContract,
        ExportQueueFile $exportQueueFileContract
    )
    {
        $this->exportQueueContract = $exportQueueContract;
        $this->exportQueueFileContract = $exportQueueFileContract;
    }

    /**
     * Send notification and clean up directories.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \Exception
     */
    public function process(\App\Models\ExportQueue $queue)
    {
        $this->setQueue($queue);
        $this->setExpedition($queue->expedition);
        $this->setActor($queue->expedition->actor);
        $this->setOwner($queue->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories();

        File::deleteDirectory($this->tmpDirectory);
        File::deleteDirectory($this->workingDirectory);

        $this->exportQueueContract->delete($queue->id);
        event('exportQueue.updated');

        ActorEventHelper::fireActorUnQueuedEvent($this->actor);

        $this->notify();

        return;
    }

    /**
     * Send notify for process completed.
     *
     * @throws \Exception
     */
    protected function notify()
    {
        $files = $this->exportQueueFileContract->getFilesWithErrorsByQueueId($this->queue->id);
        $remove = array_flip(['id', 'queue_id', 'error', 'created_at', 'updated_at']);
        $data = $files->map(function($file) use($remove){
            return array_diff_key($file->toArray(), $remove);
        });

        $message = [
            $this->expedition->title,
            trans('messages.expedition_export_complete_message', [
                'expedition' => $this->expedition->title
                ])
        ];

        $csvPath = storage_path('app/reports/'.md5($this->queue->id).'.csv');
        $csv = GeneralHelper::createCsv($data->toArray(), $csvPath);

        $this->owner->notify(new NfnExportComplete($message, $csv));
    }
}