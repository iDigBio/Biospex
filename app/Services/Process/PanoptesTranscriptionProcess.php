<?php

namespace App\Services\Process;

use App\Exceptions\CsvHeaderCountException;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use Illuminate\Validation\Factory as Validation;
use ForceUTF8\Encoding;
use App\Services\Csv\Csv;

class PanoptesTranscriptionProcess
{

    /**
     * @var mixed
     */
    protected $collection;

    /**
     * @var SubjectContract
     */
    protected $subjectContract;

    /**
     * @var PanoptesTranscriptionContract
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
     * @var TranscriptionLocationContract
     */
    private $transcriptionLocationContract;


    /**
     * NfnTranscription constructor.
     * @param SubjectContract $subjectContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     * @param TranscriptionLocationContract $transcriptionLocationContract
     * @param Validation $factory
     * @param Csv $csv
     */
    public function __construct(
        SubjectContract $subjectContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract,
        TranscriptionLocationContract $transcriptionLocationContract,
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
     * @throws CsvHeaderCountException
     */
    public function process($file)
    {
        $this->csv->readerCreateFromPath($file);
        $this->csv->setDelimiter();
        $this->csv->setEnclosure();
        $this->csv->setEscape('"');

        $header = $this->prepareHeader($this->csv->getHeaderRow());
        $rows = $this->csv->fetch();
        foreach ($rows as $row)
        {
            if (empty($row[0]))
            {
                continue;
            }
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
     * @throws CsvHeaderCountException
     */
    public function processRow($header, $row)
    {
        if (count($header) !== count($row))
        {
            throw new CsvHeaderCountException(trans('errors.csv_row_count', [
                'headers' => count($header),
                'rows'    => count($row)
            ]));
        }

        array_walk($row, function ($value)
        {
            return Encoding::toUTF8($value);
        });

        $combined = array_combine($header, $row);

        $subject = $this->getSubject($combined);

        if ($subject === null)
        {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $combined);

            return;
        }

        $this->buildTranscriptionLocation($combined, $subject);

        $combined = array_merge($combined, ['subject_projectId' => $subject->project_id]);

        $this->convertStringToIntegers($combined);

        if ($this->validateTranscription($combined))
        {
            \Log::alert('updating classification ' . $combined['classification_id']);
            $transcript = $this->panoptesTranscriptionContract->setCacheLifetime(0)
                ->findBy('classification_id', $combined['classification_id']);

            $attributes = [
                'classification_started_at' => $combined['classification_started_at'],
                'classification_finished_at' => $combined['classification_finished_at']
            ];
            $this->panoptesTranscriptionContract->update($transcript->_id, $attributes);
            return;
        }
        \Log::alert('not going to create');
        die();

        $this->panoptesTranscriptionContract->create($combined);

    }

    /**
     * @param $combined
     * @param $subject
     *
     * Transcripts: StateProvince, County
     * Subject: stateProvince, county
     */
    private function buildTranscriptionLocation($combined, $subject)
    {
        $data = [];
        foreach ($this->dwcLocalityFields as $transcriptField => $subjectField)
        {
            if (isset($combined[$transcriptField]) && ! empty($combined[$transcriptField]))
            {
                $data[decamelize($transcriptField)] = $combined[$transcriptField];

                continue;
            }

            if (isset($subject->occurrence->{$subjectField}) && ! empty($subject->occurrence->{$subjectField}))
            {
                $data[decamelize($subjectField)] = $subject->occurrence->{$subjectField};
            }
        }

        $data['classification_id'] = $combined['classification_id'];
        $data['project_id'] = $subject->project_id;
        $data['expedition_id'] = $combined['subject_expeditionId'];

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

        $attributes = ['classification_id' => $combined['classification_id']];
        $this->transcriptionLocationContract->updateOrCreateTranscriptionLocation($attributes, $data);
    }


    /**
     * Get subject from db to set projectId
     *
     * @param $combined
     * @return mixed
     */
    public function getSubject($combined)
    {
        return $this->subjectContract->setCacheLifetime(0)->find(trim($combined['subject_subjectId']));
    }

    /**
     * Convert string numbers to integers for MongoDB
     * @param $combined
     */
    public function convertStringToIntegers(&$combined)
    {
        $cols = [
            'subject_id',
            'classification_id',
            'workflow_id',
            'subject_expeditionId',
            'subject_projectId'
        ];

        foreach ($cols as $col)
        {
            if (isset($combined[$col]))
            {
                $combined[$col] = (int)$combined[$col];
            }
        }
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $combined
     * @return mixed
     */
    public function validateTranscription($combined)
    {

        $rules = ['classification_id' => 'unique_with:panoptes_transcriptions,classification_id'];
        $values = ['classification_id' => $combined['classification_id']];
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