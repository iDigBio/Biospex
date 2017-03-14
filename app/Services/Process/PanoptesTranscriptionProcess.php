<?php

namespace App\Services\Process;

use App\Exceptions\CsvHeaderCountException;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
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
     * @var
     */
    protected $csvError;

    /**
     * @var Validation
     */
    protected $factory;

    /**
     * @var
     */
    protected $expeditionId;


    /**
     * NfnTranscription constructor.
     * @param SubjectContract $subjectContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     * @param Validation $factory
     * @param Csv $csv
     */
    public function __construct(
        SubjectContract $subjectContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract,
        Validation $factory,
        Csv $csv
    )
    {
        $this->subjectContract = $subjectContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
        $this->factory = $factory;
        $this->csv = $csv;
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

        if ( ! $subject = $this->getSubject($combined))
        {
            $this->csvError[] = array_merge(['error' => 'Could not find subject id for classification'], $combined);

            return;
        }

        $combined = array_merge($combined, ['subject_projectId' => $subject->project_id]);
        $this->convertStringToIntegers($combined);

        if ($this->validateTranscription($combined))
        {
            return;
        }

        $this->panoptesTranscriptionContract->create($combined);

    }

    /**
     * Get subject from db to set projectId
     *
     * @param $combined
     * @return mixed
     */
    public function getSubject($combined)
    {
        $subject = $this->subjectContract->setCacheLifetime(0)->subjectFind(trim($combined['subject_subjectId']));

        return empty($subject) ? false : $subject;
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