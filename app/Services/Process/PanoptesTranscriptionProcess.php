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

namespace App\Services\Process;

use App\Services\Model\SubjectService;
use App\Services\Model\PanoptesTranscriptionService;
use Exception;
use Storage;
use Str;
use Validator;
use App\Services\Csv\Csv;

/**
 * Class PanoptesTranscriptionProcess
 *
 * @package App\Services\Process
 */
class PanoptesTranscriptionProcess
{
    /**
     * @var mixed
     */
    protected $collection;

    /**
     * @var SubjectService
     */
    protected $subjectService;

    /**
     * @var \App\Services\Model\PanoptesTranscriptionService
     */
    protected $panoptesTranscriptionService;

    /**
     * @var \App\Services\Process\TranscriptionLocationProcess
     */
    protected $transcriptionLocationProcess;

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
     * @param SubjectService $subjectService
     * @param \App\Services\Model\PanoptesTranscriptionService $panoptesTranscriptionService
     * @param \App\Services\Process\TranscriptionLocationProcess $transcriptionLocationProcess
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        SubjectService $subjectService,
        PanoptesTranscriptionService $panoptesTranscriptionService,
        TranscriptionLocationProcess $transcriptionLocationProcess,
        Csv $csv
    ) {
        $this->subjectService = $subjectService;
        $this->panoptesTranscriptionService = $panoptesTranscriptionService;
        $this->transcriptionLocationProcess = $transcriptionLocationProcess;
        $this->csv = $csv;

        // TODO can be removed after fixing Charles issue
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

        if (trim($row['subject_subjectId'] === null)) {
            $this->csvError[] = array_merge(['error' => 'Transcript missing subject id'], $row);
            return;
        }

        $subject = $this->subjectService->find(trim($row['subject_subjectId']));

        if ($subject === null) {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $row);
            return;
        }

        $this->transcriptionLocationProcess->buildTranscriptionLocation($row, $subject, $expeditionId);

        $row = array_merge($row, ['subject_projectId' => $subject->project_id]);

        $this->panoptesTranscriptionService->create($row);
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
     * @return string|null
     * @throws \League\Csv\CannotInsertRecord
     */
    public function checkCsvError(): ?string
    {
        if (count($this->csvError) === 0) {
            return null;
        }

        $csvName = Str::random().'.csv';

        return $this->csv->createReportCsv($this->csvError, $csvName);
    }

}