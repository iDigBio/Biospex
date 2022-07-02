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
use App\Models\Subject;
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
    private ZooniverseDbService $dbService;

    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private ActorImageService $actorImageService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private mixed $nfnCsvMap;

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
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueRepo->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 3;
        $queue->save();

        \Artisan::call('export:poll');

        $files = $this->dbService->exportQueueFileRepo->getFilesByQueueId($queue->id);

        try {
            $this->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
            $this->setDirectories();

            $this->csv->writerCreateFromTempFileObj();
            $this->csv->writer->addFormatter($this->csv->setEncoding());

            $first = true;
            $files->chunk(5)->each(function ($chunk) use (&$queue, &$first) {
                $csvData = $chunk->filter(function ($file) {
                    return $this->actorImageService->checkFile($this->tmpDirectory.'/'.$file->subject_id.'.jpg', $file->subject_id, true);
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
        $subject = $this->dbService->subjectRepo->find($file->subject_id);

        $csvArray = [];
        $presetValues = ['#expeditionId', '#expeditionTitle', 'imageName'];

        foreach ($this->nfnCsvMap as $key => $item) {
            if (in_array($key, $presetValues)) {
                $this->setPresetValues($csvArray, $key, $file, $queue);
                continue;
            }

            // If subject not found, add error column and message
            if ($subject === null) {
                $csvArray['error'] = 'Could not retrieve subject '.$file->subject_id.' from database for export';
                continue;
            }

            // If item is not array, direct translation
            if (! is_array($item)) {
                $csvArray[$key] = $item === '' ? '' : $subject->{$item};
                continue;
            }

            $csvArray[$key] = '';
            foreach ($item as $doc => $value) {
                is_array($value) ?
                    $this->setArrayValues($csvArray, $key, $doc, $value, $subject) :
                    $this->setValues($csvArray, $key, $doc, $value, $subject);
            }
        }

        $subject->exported = true;
        $subject->save();

        return $csvArray;
    }

    /**
     * Set preset values needing special attention.
     *
     * @param $csvArray
     * @param $key
     * @param \App\Models\ExportQueueFile $file
     * @param \App\Models\ExportQueue $queue
     * @return void
     */
    private function setPresetValues(&$csvArray, $key, ExportQueueFile $file, ExportQueue $queue)
    {
        if (strcasecmp($key, '#expeditionId') == 0) {
            $csvArray[$key] = $queue->expedition->id;
            return;
        }

        if (strcasecmp($key, '#expeditionTitle') == 0) {
            $csvArray[$key] = $queue->expedition->title;
            return;
        }

        if (strcasecmp($key, 'imageName') == 0) {
            $csvArray[$key] = $file->subject_id.'.jpg';
        }
    }

    /**
     * Set values of document items if array.
     *
     * @param array $csvArray
     * @param string $key
     * @param string $doc
     * @param array $array
     * @param \App\Models\Subject $subject
     * @return void
     */
    private function setArrayValues(array &$csvArray, string $key, string $doc, array $array, Subject $subject)
    {
        foreach ($array as $value) {
            if (isset($subject->{$doc}[$value])) {
                $csvArray[$key] = $subject->{$doc}[$value];
            }
        }
    }

    /**
     * Set values of document if value exists. Set special links.
     * @param array $csvArray
     * @param string $key
     * @param string $doc
     * @param string $value
     * @param \App\Models\Subject $subject
     * @return void
     */
    private function setValues(array &$csvArray, string $key, string $doc, string $value, Subject $subject)
    {
        $links = ['eol', 'mol', 'idigbio'];
        if (isset($subject->{$doc}[$value])) {
            if (in_array($key, $links)) {
                $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($subject->{$doc}[$value]), config('config.nfnSearch.'.$key));
                return;
            }

            $csvArray[$key] = $subject->{$doc}[$value];
        }
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