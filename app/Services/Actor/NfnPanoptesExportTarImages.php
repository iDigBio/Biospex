<?php

namespace App\Services\Actor;

use App\Models\ExportQueue;
use App\Repositories\Interfaces\Download;

class NfnPanoptesExportTarImages extends NfnPanoptesBase
{
    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $downloadContract;

    /**
     * NfnPanoptesExportTarImages constructor.
     *
     * @param \App\Repositories\Interfaces\Download $downloadContract
     */
    public function __construct(Download $downloadContract)
    {
        $this->downloadContract = $downloadContract;
    }

    /**
     * Create tar file.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \Exception
     */
    public function process(ExportQueue $queue)
    {
        $this->setQueue($queue);
        $this->setExpedition($queue->expedition);
        $this->setActor($queue->expedition->actor);
        $this->setOwner($queue->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories();

        exec("cd {$this->tmpDirectory} && find . \( -name '*.jpg' -o -name '*.csv' \) -print >../export.manifest");
        exec("cd {$this->tmpDirectory} && sudo tar -czf {$this->archiveExportPath} --files-from ../export.manifest", $out, $ok);

        if (! $ok) {
            $values = [
                'expedition_id' => $this->expedition->id,
                'actor_id'      => $this->actor->id,
                'file'          => $this->archiveTarGz,
                'type'          => 'export',
            ];
            $attributes = [
                'expedition_id' => $this->expedition->id,
                'actor_id'      => $this->actor->id,
                'file'          => $this->archiveTarGz,
                'type'          => 'export',
            ];

            $this->downloadContract->updateOrCreate($attributes, $values);

            $this->advanceQueue($queue);

            return;
        }

        throw new \Exception('Could not create compressed export file for Queue ID: '.$queue->id);
    }
}