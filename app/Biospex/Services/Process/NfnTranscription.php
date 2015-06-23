<?php  namespace Biospex\Services\Process;

use League\Csv\Reader;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Transcription\TranscriptionInterface;
use Validator;
use Config;

class NfnTranscription {

    /**
     * @var array
     */
    protected $header;

    /**
     * @var array
     */
    protected $csv = [];

    /**
     * Constructor.
     *
     * @param SubjectInterface $subject
     * @param TranscriptionInterface $transcription
     */
    public function __construct(SubjectInterface $subject, TranscriptionInterface $transcription)
    {
        $this->subject = $subject;
        $this->transcription = $transcription;
        $this->collection = Config::get('config.collection');
    }

    /**
     * Process csv file.
     *
     * @param $file
     * @return array
     */
    public function process($file)
    {
        $reader = Reader::createFromPath($file);
        $reader->each(function ($row, $index, $iterator) {
            return $this->processRow($row, $index);
        });

        return $this->csv;
    }

    /**
     * Process csv row.
     *
     * @param $row
     * @param $index
     * @return bool
     * @throws \Exception
     */
    public function processRow($row, $index)
    {
        if (empty($row[0]))
            return false;

        if ($index == 0)
        {
            $this->createHeader($row);

            return true;
        }

        if (count($this->header) != count($row))
            throw new \Exception(trans('emails.error_csv_row_count', ['headers' => count($this->header), 'rows' => count($row)]));

        $combined = array_combine($this->header, $row);

        // Check if fsu collection and search subjects by file name
        if ( ! $subject = $this->getSubject($combined))
            return true;

        $addArray = ['project_id' => (string) $subject->project_id, 'expedition_ids' => $subject->expedition_ids];
        $combined = $addArray + $combined;

        if ($this->validate($combined))
            return true;

        $this->transcription->create($combined);

        return true;
    }

    /**
     * Build header row.
     *
     * @param $row
     */
    public function createHeader($row)
    {
        $row[0] = 'nfn_id';
        $this->header = array_replace($row, array_fill_keys(array_keys($row, 'created_at'), 'create_date'));

        return;
    }

    /**
     * Get subject from db.
     *
     * @param $combined
     * @return mixed
     */
    public function getSubject($combined)
    {
        if ($this->checkCollection($combined))
        {
            $filename = strtok(trim($combined['filename']), '.');
            $subject = $this->subject->findByFilename($filename);
        }
        else
        {
            $subject = $this->subject->find(trim($combined['subject_id']));
        }

        if ( ! $subject)
            $this->csv[] = $combined;

        return ( ! $subject) ? false : $subject;
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
    public function validate($combined)
    {
        $rules = ['nfn_id' => 'unique:transcriptions'];
        $values = ['nfn_id' => $combined['nfn_id']];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        $fail = $validator->fails();

        if ($fail)
            $this->csv[] = $combined;

        return $fail;
    }
}
