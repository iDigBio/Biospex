<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Models\ExportQueue;
use App\Repositories\Interfaces\ExportQueueFile;
use App\Repositories\Interfaces\Subject;
use App\Services\Csv\Csv;
use Exception;

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
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * NfnPanoptesExportBuildCsv constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFile
     * @param \App\Services\Csv\Csv $csvService
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     */
    public function __construct(
        ExportQueueFile $exportQueueFile,
        Csv $csvService,
        Subject $subjectContract
    )
    {
        $this->exportQueueFile = $exportQueueFile;
        $this->nfnCsvMap = config('config.nfnCsvMap');
        $this->csvService = $csvService;
        $this->subjectContract = $subjectContract;
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
        $this->setActor($queue->expedition->actors->first());
        $this->setOwner($queue->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories();

        $files = $this->exportQueueFile->getFilesWithoutErrorByQueueId($queue->id);

        if ($files->isEmpty()) {
            throw new Exception('Missing export subjects for Queue ' . $queue->id);
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
            throw new Exception('Could not create CSV file for Queue ID '.$queue->id.' export');
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
        $subject = $this->subjectContract->find($file->subject_id);

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

            if ($subject === null) {
                $csvArray['error'] = 'Could not retrieve subject ' . $file->subject_id . ' from database for export';
                continue;
            }

            if (! is_array($item)) {
                $csvArray[$key] = $item === '' ? '' : $subject->{$item};
                continue;
            }

            $csvArray[$key] = '';
            foreach ($item as $doc => $value) {
                if (isset($subject->{$doc}[$value])) {
                    if ($key === 'eol' || $key === 'mol' || $key === 'idigbio') {
                        $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($subject->{$doc}[$value]), config('config.nfnSearch.'.$key));
                        break;
                    }

                    $csvArray[$key] = $subject->{$doc}[$value];
                    break;
                }
            }
        }

        $subject->exported = true;
        $subject->save();

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