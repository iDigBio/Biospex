<?php

namespace App\Services\Process;

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\StateCounty;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\TranscriptionLocation;
use Exception;
use Illuminate\Validation\Factory as Validation;
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
     * @var array
     */
    protected $csvError = [];

    /**
     * @var Validation
     */
    protected $factory;

    /**
     * @var
     */
    protected $expeditionId;

    /**
     * @var TranscriptionLocation
     */
    private $transcriptionLocationContract;

    /**
     * @var \App\Repositories\Interfaces\StateCounty
     */
    private $stateCountyContract;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $dwcTranscriptFields;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $dwcOccurrenceFields;

    /**
     * PanoptesTranscriptionProcess constructor.
     *
     * @param Subject $subjectContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     * @param TranscriptionLocation $transcriptionLocationContract
     * @param \App\Repositories\Interfaces\StateCounty $stateCountyContract
     * @param Validation $factory
     * @param Csv $csv
     */
    public function __construct(
        Subject $subjectContract,
        PanoptesTranscription $panoptesTranscriptionContract,
        TranscriptionLocation $transcriptionLocationContract,
        StateCounty $stateCountyContract,
        Validation $factory,
        Csv $csv
    ) {
        $this->subjectContract = $subjectContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
        $this->factory = $factory;
        $this->transcriptionLocationContract = $transcriptionLocationContract;
        $this->stateCountyContract = $stateCountyContract;
        $this->csv = $csv;
        $this->dwcTranscriptFields = $fields = config('config.dwcTranscriptFields');
        $this->dwcOccurrenceFields = $fields = config('config.dwcOccurrenceFields');
    }

    /**
     * Process csv file.
     *
     * @param $file
     */
    public function process($file)
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
                $this->processRow($header, $row);
            }
        } catch (Exception $e) {

            $this->csvError[] = $file . ': ' . $e->getMessage();

            return;
        }

        return;
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
     * @throws \Exception
     */
    public function processRow($header, $row)
    {
        if (count($header) !== count($row))
        {
            throw new Exception(trans('messages.csv_row_count', [
                'headers' => count($header),
                'rows'    => count($row)
            ]));
        }

        $subject = $this->getSubject($row);

        if ($subject === null)
        {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $row);

            return;
        }

        $this->convertStringToIntegers($row);

        $this->buildTranscriptionLocation($row, $subject);

        $row = array_merge($row, ['subject_projectId' => (int) $subject->project_id]);

        if ($this->validateTranscription($row)) {
            return;
        }

        $this->panoptesTranscriptionContract->create($row);
    }

    /**
     * @param $transcription
     * @param $subject
     *
     * Transcripts: StateProvince, County
     * Subject: stateProvince, county
     */
    private function buildTranscriptionLocation($transcription, $subject)
    {
        $data = [];
        $this->setDwcLocalityFields($transcription, $subject, $data);

        if (array_key_exists('state_province', $data) && strtolower($data['state_province']) === 'district of columbia') {
            $data['county'] = $data['state_province'];
        }

        if (! $this->checkRequiredStateCounty($data)) {
            return;
        }

        $this->prepCounty($data);
        $stateAbbr = GeneralHelper::getState($data['state_province']);
        $stateResult = $this->stateCountyContract->findByCountyState($data['county'], $stateAbbr);

        if ($stateResult === null) {
            return;
        }

        $values['classification_id'] = $transcription['classification_id'];
        $values['project_id'] = $subject->project_id;
        $values['expedition_id'] = $transcription['subject_expeditionId'];
        $values['state_county_id'] = $stateResult->id;
        $attributes = ['classification_id' => $transcription['classification_id']];

        $this->transcriptionLocationContract->updateOrCreate($attributes, $values);
    }

    /**
     * Check locality fields from transcription.
     *
     * @param $transcription
     * @param $subject
     * @param $data
     * @return array
     */
    private function setDwcLocalityFields($transcription, $subject, &$data): array
    {
        $this->setDwcLocalityFromTranscript($transcription, $data);
        $this->setDwcLocalityFromOccurrence($subject, $data);

        return $data;

    }

    /**
     * Set the dwc locality fields using transcript.
     *
     * @param $transcription
     * @param $data
     */
    private function setDwcLocalityFromTranscript($transcription, &$data)
    {
        foreach ($this->dwcTranscriptFields as $transcriptField => $mapField) {
            if (isset($transcription[$transcriptField]) && ! empty($transcription[$transcriptField])) {
                $data[$mapField] = $transcription[$transcriptField];

                continue;
            }
        }
    }

    /**
     * Set the dwc locality fields using occurrence.
     *
     * @param $subject
     * @param $data
     */
    private function setDwcLocalityFromOccurrence($subject, &$data)
    {
        if (count($data) == 2) {
            return;
        }

        foreach ($this->dwcOccurrenceFields as $occurrenceField => $mapField) {
            if (isset($subject->occurrence->{$occurrenceField}) && ! empty($subject->occurrence->{$occurrenceField})) {
                $data[$mapField] = $subject->occurrence->{$occurrenceField};

                continue;
            }
        }
    }

    /**
     * Check if state and county exist.
     *
     * @param $data
     * @return bool
     */
    private function checkRequiredStateCounty($data)
    {
        if (! isset($data['state_province']) || ! isset($data['county'])) {
            return false;
        }

        if (empty($data['state_province']) || empty($data['county'])) {
            return false;
        }

        return true;
    }

    /**
     * Prep County for searching database.
     *
     * @param $data
     */
    private function prepCounty(&$data)
    {
        $county = trim(preg_replace("/[^ \w-]/", "", $data['county']));
        $search = ['Saint', 'Sainte', 'Miami Dade', 'De Soto', 'De Kalb', 'county', 'City', 'Not Shown'];
        $replace = ['St.', 'Ste.', 'Miami-Dade', 'DeSoto', 'DeKalb', '', '', ''];
        $county = trim(str_ireplace($search, $replace, $county));
        $data['county'] = $county;
    }

    /**
     * Get subject from db to set projectId
     * Added fix for misnamed subject Id from Notes From Nature.
     *
     * @param $row
     * @return mixed
     */
    public function getSubject($row)
    {
        $value = isset($row['subject_Subject_ID']) ? $row['subject_Subject_ID'] : $row['subject_subjectId'];

        return $this->subjectContract->find(trim($value));
    }

    /**
     * Convert string numbers to integers for MongoDB
     *
     * @param $row
     */
    public function convertStringToIntegers(&$row)
    {
        $cols = collect([
            'subject_id',
            'classification_id',
            'workflow_id',
            'subject_expeditionId',
            'subject_projectId'
        ]);

        foreach ($cols as $col)
        {
            if (isset($row[$col]))
            {
                $row[$col] = (int) $row[$col];
            }
        }
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $row
     * @return mixed
     */
    public function validateTranscription($row)
    {

        $rules = ['classification_id' => 'unique:mongodb.panoptes_transcriptions,classification_id'];
        $values = ['classification_id' => $row['classification_id']];
        $validator = $this->factory->make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        return $validator->fails();
    }

    /**
     * @return array
     */
    public function getCsvError()
    {
        return $this->csvError;
    }
}