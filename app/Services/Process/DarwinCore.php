<?php

namespace App\Services\Process;

ini_set("auto_detect_line_endings", "1");
ini_set("memory_limit", "7G");
ini_set('max_execution_time', '0');
ini_set('max_input_time', '0');
set_time_limit(0);
ignore_user_abort(true);

use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\Header;
use App\Repositories\Contracts\Property;
use App\Repositories\Contracts\Meta;
use App\Repositories\Contracts\OcrQueue;
use League\Csv\Reader;
use ForceUTF8\Encoding;

class DarwinCore
{
    /**
     * @var Subject
     */
    private $subject;

    /**
     * @var Header
     */
    private $header;

    /**
     * @var Property
     */
    private $property;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var OcrQueue
     */
    private $ocr;

    /**
     * @var MetaFile
     */
    private $metaFile;

    /**
     * Header for extension and core.
     * @var
     */
    private $importHeader = [];

    /**
     * Array of duplicate subjects
     * @var array
     */
    private $duplicateArray = [];

    /**
     * Array of images with empty identifiers
     *
     * @var array
     */
    private $rejectedMultimedia = [];

    /**
     * Project Id
     *
     * @var null
     */
    private $projectId = null;

    /**
     * Header array for project
     *
     * @var
     */
    private $headerArray = [];

    /**
     * Array of identifier columns
     * @var
     */
    private $identifiers;

    /**
     * Media identifier.
     * @var
     */
    private $identifierColumn;

    /**
     * Crop for OCR
     */
    private $ocrCrop;

    /**
     * Queue to use when processing OCR.
     * @var
     */
    private $queue;

    /**
     * Disable OCR.
     *
     * @var mixed
     */
    private $disableOcr;

    /**
     * OCR data array.
     *
     * @var
     */
    private $ocrData;

    /**
     * Constructor
     *
     * @param Subject $subject
     * @param Header $header
     * @param Property $property
     * @param Meta $meta
     * @param OcrQueue $ocr
     * @param MetaFile $metaFile
     */
    public function __construct(
        Subject $subject,
        Header $header,
        Property $property,
        Meta $meta,
        OcrQueue $ocr,
        MetaFile $metaFile
    ) {
        $this->subject = $subject;
        $this->header = $header;
        $this->property = $property;
        $this->meta = $meta;
        $this->ocr = $ocr;
        $this->metaFile = $metaFile;

        $this->importHeader['core'] = [];
        $this->importHeader['extension'] = [];

        $this->identifiers = \Config::get('config.identifiers');
        $this->ocrCrop = \Config::get('config.ocr_crop');
        $this->disableOcr = \Config::get('config.disable_ocr');
        $this->queue = \Config::get('config.beanstalkd.ocr');
    }

    /**
     * Process darwin core archive.
     *
     * @param $projectId
     * @param $dir
     */
    public function process($projectId, $dir)
    {
        $this->setProjectId($projectId);

        $xml = $this->metaFile->process($dir . '/meta.xml');
        $this->saveMeta($xml);

        // Load multimedia first to create subjects
        $type = $this->metaFile->getMediaIsCore() ? 'core' : 'extension';
        $this->loadCsv($dir, $type, true);
        $this->setHeaderArray($type);
        $this->pushToQueue();

        // Load occurrence second to update subjects
        $type = $this->metaFile->getMediaIsCore() ? 'extension' : 'core';
        $this->loadCsv($dir, $type);
        $this->setHeaderArray($type);

        return;
    }

    /**
     * Load csv file.
     *
     * @param $dir
     * @param $type
     * @param bool $multimedia
     * @return array|void
     * @throws \Exception
     */
    public function loadCsv($dir, $type, $multimedia = false)
    {
        $file = $this->setFile($dir, $type);

        $reader = Reader::createFromPath($file);
        $reader->setDelimiter($this->setDelimiter($type));
        if (! empty($this->setEnclosure($type))) {
            $reader->setEnclosure($this->setEnclosure($type));
        }
        $reader->each(function ($row, $index, $iterator) use ($type, $multimedia) {
            return $this->processRow($row, $index, $type, $multimedia);
        });

        return;
    }

    /**
     * Process an individual row.
     *
     * @param $row
     * @param $index
     * @param $type
     * @param $multimedia
     * @return bool
     * @throws \Exception
     */
    public function processRow($row, $index, $type, $multimedia)
    {
        if (empty($row[0])) {
            return false;
        }

        if ($index == 0) {
            $this->handleHeader($row, $type);
            return true;
        }

        $row = $this->filterByIndex($row, $type);

        if (count($this->importHeader[$type]) != count($row)) {
            throw new \Exception(trans('emails.error_csv_row_count', ['headers' => count($this->importHeader[$type]), 'rows' => count($row)]));
        }

        array_walk($row, function (&$value) { $value = Encoding::toUTF8($value); });

        $combined = array_combine($this->importHeader[$type], $row);

        $this->stripUuidPrefix($combined, $type);

        $multimedia ? $this->saveSubject($combined, $type) : $this->saveOccurrence($combined, $type);

        return true;
    }

    /**
     * Header operations.
     *
     * @param $row
     * @param $type
     * @throws \Exception
     */
    public function handleHeader($row, $type)
    {
        $filtered = $this->filterByIndex($row, $type);
        $header = $this->buildHeaderRow($filtered, $type);
        $this->setIdentifierColumn($header, $type);
        $this->setImportHeader($header, $type);

        return;
    }

    /**
     * Build subject and save to database.
     *
     * @param $data
     * @param $type
     */
    public function saveSubject($data, $type)
    {
        $occurrenceId = $this->metaFile->getMediaIsCore() ? null : $data[$this->importHeader[$type][0]];
        $data['id'] = $this->metaFile->getMediaIsCore() ? $data[$this->importHeader[$type][0]] : $data[$this->identifierColumn];

        if ($this->reject($data)) {
            return;
        }

        $subject = ['project_id' => $this->projectId, 'ocr' => '', 'expedition_ids' => []]
                + array_merge($this->headerArray, $data)
                + ['occurrence' => is_null($occurrenceId) ? '' : $occurrenceId];

        if ($this->validateDoc($subject)) {
            return;
        }

        $subject = $this->subject->create($subject);

        if (! is_null($occurrenceId)) {
            $subject->occurrence()->save(new \App\Models\Occurrence(['id' => $occurrenceId]));
        }

        if ($this->disableOcr) {
            return;
        }

        $this->buildOcrQueue($subject);
    }

    /**
     * Add to rejected media if subject id is not determined.
     *
     * @param $data
     * @return bool
     */
    public function reject($data)
    {
        if (empty($data['id'])) {
            $this->rejectedMultimedia[] = $data;
            return true;
        }

        return false;
    }

    /**
     * Validate if subject exists using project_id and id.
     * Validator->fails() returns true if validation fails.
     *
     * @param $subject
     * @return bool
     */
    public function validateDoc($subject)
    {
        $rules = ['project_id' => 'unique_with:subjects,id'];
        $values = ['project_id' => $subject['project_id'], 'id' => $subject['id']];

        $validator = \Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        $fail = $validator->fails();

        if ($fail) {
            $this->unsetSubjectVariables($subject);
            $this->duplicateArray[] = $subject;
        }

        return $fail;
    }

    /**
     * Push ocrData to queue.
     */
    public function pushToQueue()
    {
        if ($this->disableOcr) {
            return;
        }

        $id = $this->saveOcrQueue($this->ocrData, count($this->ocrData));
        \Queue::push('App\Services\Queue\QueueFactory', ['id' => $id, 'class' => 'OcrProcessQueue'], $this->queue);

        return;
    }

    /**
     * Save Occurrence.
     *
     * @param $data
     * @param $type
     */
    public function saveOccurrence($data, $type)
    {
        $subjects = $this->subject->findByProjectOccurrenceId($this->projectId, $data[$this->importHeader[$type][0]]);

        if ($subjects->isEmpty()) {
            return;
        }

        foreach ($subjects as $subject) {
            $subject->occurrence()->save(new \App\Models\Occurrence($data));
        }

        return;
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $subject
     */
    public function buildOcrQueue($subject)
    {
        $this->ocrData[$subject->_id] = [
            'crop'   => $this->ocrCrop,
            'ocr'    => '',
            'status' => 'pending',
            'url'    => $subject->accessURI
        ];

        return;
    }

    /**
     * Save OCR data for later processing.
     *
     * @param $data
     * @param $count
     * @return mixed
     */
    public function saveOcrQueue($data, $count)
    {
        $queue = $this->ocr->create([
            'project_id' => $this->projectId,
            'data' => json_encode(['subjects' => $data]),
            'subject_count' => $count
        ]);

        return $queue->id;
    }

    /**
     * Filters array by index so number of columns match existing MetaFields.
     *
     * @param $row
     * @param $type
     * @return array
     */
    public function filterByIndex($row, $type)
    {
        $result = array_intersect_key($row, $this->metaFile->getMetaFields($type));

        return $result;
    }

    /**
     * Build header csv file so it matches qualified names
     * and set the multimediaIdentifier string value if media is not core.
     *
     * @param $row
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function buildHeaderRow($row, $type)
    {
        foreach ($this->metaFile->getMetaFields($type) as $key => $qualified) {
            if (! isset($row[$key])) {
                throw new \Exception(trans('emails.error_csv_build_header', ['key' => $key, 'qualified' => $qualified]));
            }

            $short = $this->checkProperty($qualified, $row[$key]);
            $header[$key] = $short;
        }

        return $header;
    }

    /**
     * Check property for qualified and short name. Create when necessary.
     *
     * @param $qualified
     * @param $ns_short
     * @return string
     */
    public function checkProperty($qualified, $ns_short)
    {
        if ($qualified == 'id' || $qualified == 'coreid') {
            return $qualified;
        }

        list($namespace, $short) = preg_match('/:/', $ns_short) ? preg_split('/:/', $ns_short) : ['', $ns_short];

        $checkQualified = $this->property->findByQualified($qualified);
        $checkShort = $this->property->findByShort($short);

        // Return if qualified exists and short is the same.
        if (! is_null($checkQualified)) {
            $short = $checkQualified->short;
        }
        // Create using new short if qualified is null and short exists.
        elseif (is_null($checkQualified) && ! is_null($checkShort)) {
            $short .= substr(md5(uniqid(mt_rand(), true)), 0, 4);
            $array = [
                'qualified' => $qualified,
                'short'     => $short,
                'namespace' => $namespace,
            ];
            $this->property->create($array);
        }
        // Create if neither exist using same short
        elseif (is_null($checkQualified) && is_null($checkShort)) {
            $array = [
                'qualified' => $qualified,
                'short'     => $short,
                'namespace' => $namespace,
            ];
            $this->property->create($array);
        }

        return $short;
    }

    /**
     * Unset unnecessary variables when creating csv.
     *
     * @param $subject
     */
    private function unsetSubjectVariables(&$subject)
    {
        unset($subject['project_id']);
        unset($subject['ocr']);
        unset($subject['expedition_ids']);
        unset($subject['occurrence']);
    }

    /**
     * Set project id being processed
     *
     * @param $id
     */
    public function setProjectId($id)
    {
        $this->projectId = $id;
    }

    /**
     * Set the identifier column
     *
     * @param $header
     * @param $type
     */
    private function setIdentifierColumn($header, $type)
    {
        if (! $this->metaFile->getMediaIsCore() && $type == 'core') {
            return;
        }

        if (! $result = array_values(array_intersect($this->identifiers, $header))) {
            return;
        }

        $this->identifierColumn = $result[0];

        return;
    }

    /**
     * Set display header properties.
     *
     * @param $header
     * @param $type
     */
    private function setImportHeader($header, $type)
    {
        $this->importHeader[$type] = $header;

        return;
    }

    /**
     * Set header array and update/save.
     *
     * @param $type
     */
    public function setHeaderArray($type)
    {
        $result = $this->header->getByProjectId($this->projectId);

        $header = $this->importHeader[$type];

        $headerFields = array_map(function () {}, array_flip($header));

        $mediaIsCore = $this->metaFile->getMediaIsCore();
        if (($mediaIsCore && $type == 'core') && ! in_array('ocr', $headerFields)) {
            $headerFields['ocr'] = '';
        }

        if ((! $mediaIsCore && $type == 'extension') && ! in_array('ocr', $headerFields)) {
            $headerFields['ocr'] = '';
        }

        if (is_null($result)) {
            $this->headerArray = $headerFields;
            $array = [
                'project_id' => $this->projectId,
                'header'     => json_encode($this->headerArray),
            ];
            $this->header->create($array);
        } else {
            $this->headerArray = array_merge(json_decode($result->header, true), $headerFields);
            $result->header = json_encode($this->headerArray);
            $result->save();
        }

        return;
    }

    /**
     * Strip prefixes from uuids.
     *
     * @param $combined
     * @param $type
     */
    private function stripUuidPrefix(&$combined, $type)
    {
        $metaFields = $this->metaFile->getMetaFields();

        if (isset($combined[$this->identifierColumn]) && ! empty($combined[$this->identifierColumn])) {
            $combined[$this->identifierColumn] = substr($combined[$this->identifierColumn], -36);
        }

        $combined[$metaFields[$type][0]] = substr($combined[$metaFields[$type][0]], -36);

        return;
    }

    /**
     * Save meta data for this upload.
     *
     * @param $xml
     */
    public function saveMeta($xml)
    {
        $this->meta->create([
            'project_id' => $this->projectId,
            'xml'        => $xml,
        ]);

        return;
    }

    /**
     * Return duplicate array
     *
     * @return array
     */
    public function getDuplicates()
    {
        return $this->duplicateArray;
    }

    /**
     * Return empty UUID array
     *
     * @return array
     */
    public function getRejectedMedia()
    {
        return $this->rejectedMultimedia;
    }

    /**
     * Set File to work with.
     *
     * @param $dir
     * @param $type
     * @return string
     */
    public function setFile($dir, $type)
    {
        $file = ($type == 'core') ? $dir . "/" . $this->metaFile->getCoreFile() : $dir . "/" . $this->metaFile->getExtensionFile();

        return $file;
    }

    /**
     * Set delimiter
     *
     * @param $type
     * @return mixed
     */
    public function setDelimiter($type)
    {
        $delimiter = ($type == 'core') ? $this->metaFile->getCoreDelimiter() : $this->metaFile->getExtDelimiter();

        return $delimiter;
    }

    /**
     * Set enclosure
     *
     * @param $type
     * @return mixed
     */
    public function setEnclosure($type)
    {
        $enclosure = ($type == 'core') ? $this->metaFile->getCoreEnclosure() : $this->metaFile->getExtEnclosure();

        return $enclosure;
    }
}
