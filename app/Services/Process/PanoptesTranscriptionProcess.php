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

namespace App\Services\Process;

use App\Services\Model\TranscriptionLocationStateCountyService;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\PanoptesTranscription;
use Exception;
use Storage;
use Str;
use Validator;
use App\Services\Csv\Csv;

class PanoptesTranscriptionProcess
{
    /**
     * @var mixed
     */
    protected $collection;

    /**
     * @var Subject
     */
    protected $subjectContract;

    /**
     * @var PanoptesTranscription
     */
    protected $panoptesTranscriptionContract;

    /**
     * @var \App\Services\Model\TranscriptionLocationStateCountyService
     */
    protected $locationStateCountyService;

    /**
     * @var array
     */
    protected $csvError = [];

    /**
     * @var null
     */
    public $csvFile = null;

    /**
     * @var \App\Services\Csv\Csv
     */
    protected $csv;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected $nfnMisMatched;

    /**
     * PanoptesTranscriptionProcess constructor.
     *
     * @param Subject $subjectContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     * @param \App\Services\Model\TranscriptionLocationStateCountyService $locationStateCountyService
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        Subject $subjectContract,
        PanoptesTranscription $panoptesTranscriptionContract,
        TranscriptionLocationStateCountyService $locationStateCountyService,
        Csv $csv
    ) {
        $this->subjectContract = $subjectContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
        $this->locationStateCountyService = $locationStateCountyService;
        $this->csv = $csv;

        $this->nfnMisMatched = config('config.nfnMisMatched');
    }

    /**
     * Process csv file.
     *
     * @param $file
     * @param $expeditionId
     */
    public function process($file, $expeditionId)
    {
        try {
            $this->csv->readerCreateFromPath($file);
            $this->csv->setDelimiter();
            $this->csv->setEnclosure();
            $this->csv->setEscape('"');
            $this->csv->setHeaderOffset();

            $header = $this->prepareHeader($this->csv->getHeader());
            $rows = $this->csv->getRecords($header);
            foreach ($rows as $offset => $row) {
                $this->processRow($header, $row, $expeditionId);
            }

            return;
        } catch (Exception $e) {

            $this->csvError[] = ['error' => $file . ': ' . $e->getMessage() . ', Line: ' . $e->getLine()];

            return;
        }
    }

    /**
     * Prepare header
     * Replace created_at column with create_date to avoid DB issues.
     *
     * @param $header
     * @return array
     */
    protected function prepareHeader($header)
    {
        return array_replace($header, array_fill_keys(array_keys($header, 'created_at'), 'create_date'));
    }

    /**
     * Process an individual row
     *
     * @param $header
     * @param $row
     * @param $expeditionId
     */
    public function processRow($header, $row, $expeditionId)
    {
        if (count($header) !== count($row))
        {
            $message = t('Header column count does not match row count. :headers headers / :rows rows', [
                ':headers' => count($header),
                ':rows'    => count($row)
            ]);

            $this->csvError[] = ['error' => $message];

            return;
        }

        if ($this->validateTranscription($row['classification_id'])) {
            return;
        }

        /*
         * @TODO This can be removed once Charles expedition has processed
         */
        $this->fixMisMatched($row, $expeditionId);

        $subject = $this->subjectContract->find(trim($row['subject_subjectId']));

        if ($subject === null)
        {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $row);

            return;
        }

        $this->locationStateCountyService->buildTranscriptionLocation($row, $subject, $expeditionId);

        $row = array_merge($row, ['subject_projectId' => $subject->project_id]);

        $this->panoptesTranscriptionContract->create($row);
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $classification_id
     * @return mixed
     */
    public function validateTranscription($classification_id)
    {
        $rules = ['classification_id' => 'unique:mongodb.panoptes_transcriptions,classification_id'];
        $values = ['classification_id' => (int) $classification_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if record exists.
        return $validator->fails();
    }

    /**
     * Fix mismatched column names.
     *
     * @TODO This can be removed once Charles expedition has processed
     * @param $row
     * @param $expeditionId
     */
    private function fixMisMatched(&$row, $expeditionId)
    {
        foreach ($this->nfnMisMatched as $key => $value) {
            if (isset($row[$key])) {
                $row[$value] = $row[$key];
            }
        }

        if (!isset($row['subject_expeditionId'])) {
            $row['subject_expeditionId'] = $expeditionId;
        }
    }

    /**
     * Check errors.
     *
     * @return bool
     * @throws \League\Csv\CannotInsertRecord
     * @see \App\Jobs\NfnClassificationTranscriptJob
     */
    public function checkCsvError(): bool
    {
        if (count($this->csvError) === 0) {
            return false;
        }

        $csvCollection = collect($this->csvError);
        $first = $csvCollection->first();
        $header = array_keys($first);

        $this->csvFile = Storage::path(config('config.reports_dir') . '/' . Str::random() . '.csv');
        $this->csv->writerCreateFromPath($this->csvFile);
        $this->csv->insertOne($header);
        $this->csv->insertAll($csvCollection->toArray());

        return true;
    }

}