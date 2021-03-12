<?php
/*
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

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Csv\Csv;
use Illuminate\Support\LazyCollection;
use Exception;

/**
 * Class ZooniverseBuildCsv
 *
 * @package App\Services\Actor
 */
class ZooniverseBuildCsv extends ZooniverseBase implements ActorInterface
{
    /**
     * @var \App\Services\Actor\ZooniverseDbService
     */
    private $dbService;

    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private $actorImageService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $nfnCsvMap;

    /**
     * ZooniverseBuildCsv constructor.
     *
     * @param \App\Services\Actor\ZooniverseDbService $dbService
     * @param \App\Services\Actor\ActorImageService $actorImageService
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        ZooniverseDbService $dbService,
        ActorImageService $actorImageService,
        Csv $csv
    ) {
        $this->dbService = $dbService;
        $this->actorImageService = $actorImageService;
        $this->csv = $csv;
        $this->nfnCsvMap = config('config.nfnCsvMap');
    }

    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueService->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 3;
        $queue->save();

        $files = $this->dbService->exportQueueFileService->getFilesByQueueId($queue->id);

        try {
            $this->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
            $this->setDirectories();

            $this->csv->writerCreateFromTempFileObj();
            $this->csv->writer->addFormatter($this->csv->setEncoding());

            $first = true;
            $files->chunk(5)->each(function ($chunk) use (&$queue, &$first) {
                $csvData = $chunk->filter(function ($file) {
                    return $this->actorImageService->checkFile($this->tmpDirectory.'/'.$file->subject_id.'.jpg', true);
                })->map(function ($file) use ($queue) {
                    return $this->mapNfnCsvColumns($file, $queue);
                });

                if (empty($csvData)) {
                    throw new Exception(t('CSV data empty while creating file for Expedition ID: %s', $queue->expedition->id));
                }
                $this->buildCsv($csvData, $first);
                $first = false;

                $queue->processed = $queue->processed + $chunk->count();
                $queue->save();
            });

            $csvFileName = $queue->expedition->uuid.'.csv';
            $csvFilePath = $this->tmpDirectory.'/'.$csvFileName;
            \File::put($csvFilePath, $this->csv->writer->getContent());

            $this->dbService->updateRejected($this->actorImageService->getRejected());

            if (!$this->checkCsvImageCount($queue)) {
                throw new Exception(t('The row count in the csv export file does not match image count.'));
            }

        } catch (Exception $exception) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Map nfn csvExport values.
     *
     * @param \App\Models\ExportQueueFile $file
     * @param \App\Models\ExportQueue $queue
     * @return array
     */
    public function mapNfnCsvColumns(ExportQueueFile $file, ExportQueue $queue): array
    {
        $subject = $this->dbService->subjectService->find($file->subject_id);

        $csvArray = [];
        foreach ($this->nfnCsvMap as $key => $item) {
            if ($key === '#expeditionId') {
                $csvArray[$key] = $queue->expedition->id;
                continue;
            }
            if ($key === '#expeditionTitle') {
                $csvArray[$key] = $queue->expedition->title;
                continue;
            }
            if ($key === 'imageName') {
                $csvArray[$key] = $file->subject_id.'.jpg';
                continue;
            }

            if ($subject === null) {
                $csvArray['error'] = 'Could not retrieve subject '.$file->subject_id.' from database for export';
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
     * @param \Illuminate\Support\LazyCollection $data
     * @param bool $first
     * @throws \League\Csv\CannotInsertRecord
     */
    private function buildCsv(LazyCollection $data, bool $first = false)
    {
        if ($first) {
            $this->csv->insertOne(array_keys($data->first()));
        }

        $this->csv->insertAll($data);
    }

    /**
     * Check csv row count to image count.
     * Do not set csv header offset. Since csv is in same dir as image, it will add 1 to the count.
     *
     * @param \App\Models\ExportQueue $queue
     * @return bool
     */
    private function checkCsvImageCount(ExportQueue $queue): bool
    {
        $this->csv->readerCreateFromPath($this->tmpDirectory . '/' . $queue->expedition->uuid . '.csv');
        $csvCount = $this->csv->getReaderCount();

        $fi = new \FilesystemIterator($this->tmpDirectory);
        $dirFileCount = iterator_count($fi);

        return $csvCount === $dirFileCount;
    }
}