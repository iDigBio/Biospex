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

namespace App\Services\Transcriptions;

use TranscriptionMap;
use App\Services\Models\PanoptesTranscriptionModelService;
use App\Services\Models\SubjectModelService;
use App\Services\Csv\AwsS3CsvService;
use App\Services\Process\CreateReportService;
use Exception;
use Str;
use Validator;

/**
 * Class CreatePanoptesTranscriptionService
 *
 * @package App\Services\Transcriptions
 */
class CreatePanoptesTranscriptionService
{
    /**
     * @var mixed
     */
    protected mixed $collection;

    /**
     * @var array
     */
    protected array $csvError = [];

    /**
     * CreatePanoptesTranscriptionService constructor.
     * Used in overnight scripts to create transcriptions from csv to mongodb.
     *
     * @param \App\Services\Models\SubjectModelService $subjectModelService
     * @param \App\Services\Models\PanoptesTranscriptionModelService $panoptesTranscriptionModelService
     * @param \App\Services\Transcriptions\CreateTranscriptionLocationService $createTranscriptionLocationService
     * @param \App\Services\Process\CreateReportService $createReportService
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     */
    public function __construct(
        private SubjectModelService $subjectModelService,
        private PanoptesTranscriptionModelService $panoptesTranscriptionModelService,
        private CreateTranscriptionLocationService $createTranscriptionLocationService,
        private CreateReportService $createReportService,
        private AwsS3CsvService $awsS3CsvService
    ) {}

    /**
     * Process transcription csv file and enter into MongoDB.
     *
     * @param $file
     * @param $expeditionId
     */
    public function process($file, $expeditionId)
    {
        try {
            $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $file, 'r');
            $this->awsS3CsvService->createCsvReaderFromStream();
            $this->awsS3CsvService->csv->setDelimiter();
            $this->awsS3CsvService->csv->setEnclosure();
            $this->awsS3CsvService->csv->setEscape('"');
            $this->awsS3CsvService->csv->setHeaderOffset();

            $header = $this->prepareHeader($this->awsS3CsvService->csv->getHeader());
            $rows = $this->awsS3CsvService->csv->getRecords($header);

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
    protected function prepareHeader($header): array
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

        if (trim($row['subject_subjectId'] === null)) {
            $this->csvError[] = array_merge(['error' => 'Transcript missing subject id'], $row);
            return;
        }

        $subject = $this->subjectModelService->find(trim($row['subject_subjectId']));

        if ($subject === null) {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $row);
            return;
        }

        $this->createTranscriptionLocationService->buildTranscriptionLocation($row, $subject, $expeditionId);

        $row = array_merge($row, ['subject_projectId' => $subject->project_id]);

        $rowWithEncodeHeaders = collect($row)->mapWithKeys(function($value, $field){
            $newField = TranscriptionMap::encodeTranscriptionField($field);
            return [$newField => $value];
        })->toArray();

        $this->panoptesTranscriptionModelService->create($rowWithEncodeHeaders);
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

        return $this->createReportService->createCsvReport($csvName, $this->csvError);
    }

}