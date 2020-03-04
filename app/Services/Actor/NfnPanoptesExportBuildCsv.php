<?php

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Models\ExportQueue;
use App\Repositories\Interfaces\ExportQueueFile;
use App\Services\Csv\Csv;

class NfnPanoptesExportBuildCsv extends NfnPanoptesBase
{
    /**
     * @var \App\Repositories\Interfaces\ExportQueueFile
     */
    private $exportQueueFile;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $nfnCsvMap;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csvService;

    /**
     * NfnPanoptesExportBuildCsv constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFile
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        ExportQueueFile $exportQueueFile,
        Csv $csvService
    )
    {
        $this->exportQueueFile = $exportQueueFile;
        $this->nfnCsvMap = config('config.nfnCsvMap');
        $this->csvService = $csvService;
    }

    /**
     * Build csv file.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \League\Csv\CannotInsertRecord
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

        $files = $this->exportQueueFile->getFilesWithoutErrorByQueueId($queue->id);

        if ($files->isEmpty()) {
            throw new \Exception('Missing export subjects for Queue ' . $queue->id);
        }

        $csvExport = $files->filter(function ($file) {
            if($this->checkConvertedFile($file->subject_id, true)){
                ActorEventHelper::fireActorProcessedEvent($this->actor);

                return true;
            }

            return false;
        })->map(function ($file) {
            ActorEventHelper::fireActorProcessedEvent($this->actor);

            return $this->mapNfnCsvColumns($file);
        });

        if (! $this->createCsv($csvExport->toArray())) {
            throw new \Exception('Could not create CSV file for Queue ID '.$queue->id.' export');
        }

        ActorEventHelper::fireActorQueuedEvent($this->actor);

        $this->advanceQueue($queue);

        return;
    }

    /**
     * Map nfn csvExport values.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return array
     */
    public function mapNfnCsvColumns(\App\Models\ExportQueueFile $file)
    {
        $csvArray = [];
        foreach ($this->nfnCsvMap as $key => $item) {
            if ($key === '#expeditionId') {
                $csvArray[$key] = $this->expedition->id;
                continue;
            }
            if ($key === '#expeditionTitle') {
                $csvArray[$key] = $this->expedition->title;
                continue;
            }
            if ($key === 'imageName') {
                $csvArray[$key] = $file->subject_id.'.jpg';
                continue;
            }
            if (! is_array($item)) {
                $csvArray[$key] = $item === '' ? '' : $file->subject->{$item};
                continue;
            }

            $csvArray[$key] = '';
            foreach ($item as $doc => $value) {
                if (isset($file->subject->{$doc}->{$value})) {
                    if ($key === 'eol' || $key === 'mol' || $key === 'idigbio') {
                        $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($file->subject->{$doc}->{$value}), config('config.nfnSearch.'.$key));
                        break;
                    }

                    $csvArray[$key] = $file->subject->{$doc}->{$value};
                    break;
                }
            }
        }

        return $csvArray;
    }

    /**
     * Create csv file.
     *
     * @param $csvExport
     * @return bool
     * @throws \League\Csv\CannotInsertRecord
     * @throws \TypeError
     */
    private function createCsv($csvExport)
    {
        if (0 === count($csvExport)) {
            return false;
        }

        $csvFileName = $this->expedition->uuid.'.csv';
        $csvFilePath = $this->tmpDirectory.'/'.$csvFileName;
        $this->csvService->writerCreateFromPath($csvFilePath);
        $this->csvService->insertOne(array_keys(reset($csvExport)));
        $this->csvService->insertAll($csvExport);

        return true;
    }
}