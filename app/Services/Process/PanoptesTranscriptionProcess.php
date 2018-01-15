<?php

namespace App\Services\Process;

use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\TranscriptionLocation;
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
     * @var array
     */
    protected $dwcLocalityFields;

    /**
     * @var TranscriptionLocation
     */
    private $transcriptionLocationContract;


    /**
     * NfnTranscription constructor.
     * @param Subject $subjectContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     * @param TranscriptionLocation $transcriptionLocationContract
     * @param Validation $factory
     * @param Csv $csv
     */
    public function __construct(
        Subject $subjectContract,
        PanoptesTranscription $panoptesTranscriptionContract,
        TranscriptionLocation $transcriptionLocationContract,
        Validation $factory,
        Csv $csv
    )
    {
        $this->subjectContract = $subjectContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
        $this->factory = $factory;
        $this->csv = $csv;
        $this->dwcLocalityFields = $fields = config('config.dwcLocalityFields');
        $this->transcriptionLocationContract = $transcriptionLocationContract;
    }

    /**
     * Process csv file.
     *
     * @param $file
     * @throws \Exception
     */
    public function process($file)
    {
        $this->csv->readerCreateFromPath($file);
        $this->csv->setDelimiter();
        $this->csv->setEnclosure();
        $this->csv->setEscape('"');
        $this->csv->setHeaderOffset();

        $header = $this->prepareHeader($this->csv->getHeader());
        $rows = $this->csv->getRecords($header);
        foreach ($rows as $offset => $row)
        {
            $this->processRow($header, $row);
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
     * @param $header
     * @param $row
     * @throws \Exception
     */
    public function processRow($header, $row)
    {
        if (count($header) !== count($row))
        {
            throw new \Exception(trans('errors.csv_row_count', [
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

        $this->buildTranscriptionLocation($row, $subject);

        $row = array_merge($row, ['subject_projectId' => $subject->project_id]);

        $this->convertStringToIntegers($row);

        if ($this->validateTranscription($row))
        {
            return;
        }

        $this->panoptesTranscriptionContract->create($row);

    }

    /**
     * @param $row
     * @param $subject
     *
     * Transcripts: StateProvince, County
     * Subject: stateProvince, county
     */
    private function buildTranscriptionLocation($row, $subject)
    {
        $data = [];
        foreach ($this->dwcLocalityFields as $transcriptField => $subjectField)
        {
            if (isset($row[$transcriptField]) && ! empty($row[$transcriptField]))
            {
                $data[decamelize($transcriptField)] = $row[$transcriptField];

                continue;
            }

            if (isset($subject->occurrence->{$subjectField}) && ! empty($subject->occurrence->{$subjectField}))
            {
                $data[decamelize($subjectField)] = $subject->occurrence->{$subjectField};
            }
        }

        $data['classification_id'] = $row['classification_id'];
        $data['project_id'] = $subject->project_id;
        $data['expedition_id'] = $row['subject_expeditionId'];

        if (array_key_exists('state_province', $data) && strtolower($data['state_province']) === 'district of columbia')
        {
            $data['county'] = $data['state_province'];
        }

        $data['state_county'] = empty($data['state_province']) || empty($data['county']) ?
            null : get_state($data['state_province']) . '-' . trim(str_ireplace('county', '', $data['county']));

        if (null === $data['state_county'])
        {
            return;
        }

        $attributes = ['classification_id' => $row['classification_id']];
        $this->transcriptionLocationContract->updateOrCreate($attributes, $data);
    }


    /**
     * Get subject from db to set projectId
     *
     * @param $row
     * @return mixed
     */
    public function getSubject($row)
    {
        return $this->subjectContract->find(trim($row['subject_subjectId']));
    }

    /**
     * Convert string numbers to integers for MongoDB
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

        $rules = ['classification_id' => 'unique_with:panoptes_transcriptions,classification_id'];
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