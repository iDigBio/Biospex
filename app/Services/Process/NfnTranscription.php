<?php  namespace Biospex\Services\Process;

use Biospex\Repositories\Contracts\Subject;
use Biospex\Repositories\Contracts\Transcription;
use Biospex\Services\Csv\CsvAbstract;
use Illuminate\Config\Repository as Config;
use Illuminate\Validation\Factory as Validation;
use ForceUTF8\Encoding;

class NfnTranscription extends CsvAbstract
{

    /**
     * @var mixed
     */
    protected $collection;

    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var Transcription
     */
    protected $transcription;

    /**
     * @var
     */
    protected $csv;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Validator
     */
    protected $factory;


    /**
     * Constructor
     * NfnTranscription constructor.
     * @param Subject $subject
     * @param Transcription $transcription
     * @param Config $config
     * @param Validator $factory
     */
    public function __construct(
        Subject $subject,
        Transcription $transcription,
        Config $config,
        Validation $factory
    )
    {
        $this->config = $config;
        $this->collection = $this->config->get('config.collection');
        $this->subject = $subject;
        $this->transcription = $transcription;
        $this->factory = $factory;
    }

    /**
     * Process csv file.
     *
     * @param $file
     * @return array
     */
    public function process($file)
    {
        $this->readerCreateFromPath($file, ",", '"');

        $header = $this->prepareHeader($this->getHeaderRow());

        $rows = $this->fetch();
        foreach ($rows as $row)
        {
            if (empty($row[0]))
            {
                continue;
            }
            $this->processRow($header, $row);
        }

        return $this->csv;
    }

    /**
     * Prepare header
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
        if ( ! $this->testHeaderRowCount($header, $row))
        {
            return;
        }

        array_walk($row, function (&$value)
        {
            $value = Encoding::toUTF8($value);
        });

        $combined = $this->combineHeaderAndRow($header, $row);

        if ($this->validateTranscription($combined)) {
            return;
        }

        if (! $subject = $this->getSubject($combined)) {
            $this->csv[] = $combined;

            return;
        }

        $addArray = ['project_id' => $subject->project_id, 'expedition_ids' => $subject->expedition_ids];
        $combined = $addArray + $combined;

        $this->transcription->create($combined);

        return;
    }

    /**
     * Test header and row count are equal for combine
     * @param $header
     * @param $row
     * @return bool
     */
    public function testHeaderRowCount(&$header, &$row)
    {
        if (count($header) != count($row))
        {
            $this->fixHeaderAndRowCount($header, $row);
            $combined = $this->combineHeaderAndRow($header, $row);
            $this->csv[] = $combined;

            return false;
        }

        return true;
    }

    /**
     * @param $header
     * @param $row
     * @return array
     */
    public function combineHeaderAndRow($header, $row)
    {
        return array_combine($header, $row);
    }

    /**
     * Get subject from db
     * If set collection exists, use filename to find subject
     * @param $combined
     * @return mixed
     */
    public function getSubject($combined)
    {
        if ($this->checkCollection($combined)) {
            $filename = strtok(trim($combined['filename']), '.');
            $subject = $this->subject->findByFilename($filename);
        } else {
            $subject = $this->subject->find(trim($combined['subject_id']));
        }

        return empty($subject) ? false : $subject;
    }

    /**
     * Check if FSU collection.
     *
     * @param $combined
     * @return bool
     */
    public function checkCollection($combined)
    {
        return strtolower(trim($combined['collection'])) == $this->collection;
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $combined
     * @return mixed
     */
    public function validateTranscription($combined)
    {

        $rules = ['id' => 'unique_with:transcriptions,id'];
        $values = ['id' => $combined['id']];
        $validator = $this->factory->make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        $fail = $validator->fails();

        return $fail;
    }

    /**
     * Fix header and row count so the match.
     * @param $header
     * @param $row
     */
    protected function fixHeaderAndRowCount(&$header, &$row)
    {
        $headerCount = count($header);
        $rowCount = count($row);

        if ($headerCount < $rowCount)
        {
            $count = $rowCount - $headerCount;
            $this->addDummyValuesToArray($header, $count);
        }
        else
        {
            $count = $headerCount - $rowCount;
            $this->addDummyValuesToArray($row, $count);
        }
    }

    /**
     * Loop through and add dummy value to array
     * @param $array
     * @param $count
     */
    protected function addDummyValuesToArray(&$array, $count)
    {
        for ($i = 0; $i < $count; $i++)
        {
            $array[] = 'dummy_value_' . $i;
        }
    }
}
