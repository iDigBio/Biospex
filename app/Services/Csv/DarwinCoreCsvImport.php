<?php

namespace App\Services\Csv;

use App\Interfaces\Header;
use App\Interfaces\Property;
use App\Interfaces\Subject;
use Illuminate\Validation\Factory as Validation;
use App\Models\Occurrence;

class DarwinCoreCsvImport
{

    /**
     * @var Property
     */
    public $propertyContract;

    /**
     * @var Subject
     */
    public $subjectContract;

    /**
     * @var Header
     */
    public $headerContract;

    /**
     * Array for meta file fields: core and extension
     *
     * @var array
     */
    public $metaFields;

    /**
     * Whether media is core or extension in meta file
     *
     * @var bool
     */
    public $mediaIsCore;

    /**
     * Type: core or extension
     *
     * @var string
     */
    public $type;

    /**
     * Id of project
     *
     * @var bool
     */
    public $projectId;

    /**
     * Rejected multimedia array
     *
     * @var array
     */
    public $rejectedMultimedia;

    /**
     * Duplicate images array
     *
     * @var array
     */
    public $duplicateArray;

    /**
     * @var Validation
     */
    public $factory;

    /**
     * @var array
     */
    public $identifiers;

    /**
     * @var \App\Services\Csv\Csv
     */
    public $csv;

    /**
     * @var int
     */
    public $subjectCount = 0;

    /**
     * @var
     */
    public $identifierColumn;

    /**
     * @var
     */
    public $header;

    /**
     * Construct
     *
     * @param Property $propertyContract
     * @param Subject $subjectContract
     * @param Header $headerContract
     * @param Validation $factory
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        Property $propertyContract,
        Subject $subjectContract,
        Header $headerContract,
        Validation $factory,
        Csv $csv
    )
    {
        $this->identifiers = config('config.dwcRequiredFields.extension.identifier');
        $this->propertyContract = $propertyContract;
        $this->subjectContract = $subjectContract;
        $this->headerContract = $headerContract;
        $this->factory = $factory;
        $this->csv = $csv;
    }

    /**
     * Set meta properties ascertained in DarwinCoreImport and needed for processing csv file
     *
     * @param $mediaIsCore
     * @param $metaFields
     * @param $projectId
     */
    public function setCsvMetaProperties($mediaIsCore, $metaFields, $projectId)
    {
        $this->mediaIsCore = $mediaIsCore;
        $this->metaFields = $metaFields;
        $this->projectId = $projectId;
    }

    /**
     * Load a csv file
     *
     * @param $file
     * @param $delimiter
     * @param $enclosure
     * @param $type
     * @param $loadMedia
     * @throws \Exception
     */
    public function loadCsvFile($file, $delimiter, $enclosure, $type, $loadMedia)
    {
        $this->csv->readerCreateFromPath($file);
        $this->csv->setDelimiter($delimiter);
        $this->csv->setEnclosure($enclosure);
        $this->csv->setHeaderOffset();

        $this->header = $this->processCsvHeader($this->csv->getHeader(), $type);

        $rows = $this->csv->getRecords($this->header);
        foreach ($rows as $offset => $row)
        {
            $this->processRow($row, $type, $loadMedia);
        }

        $this->saveHeaderArray($this->header, $loadMedia);

        unset($rows);
        unset($this->header);
        unset($this->csv->reader);
    }

    /**
     * Process an individual row.
     *
     * @param $row
     * @param $type
     * @param $loadMedia
     * @throws \Exception
     */
    public function processRow($row, $type, $loadMedia)
    {
        $this->testHeaderRowCount($row);

        $loadMedia ?
            $this->prepareSubject($row, $this->metaFields[$type])
            : $this->saveOccurrence($row);
    }

    /**
     * Process a csv header
     *
     * @param $header
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function processCsvHeader($header, $type)
    {
        $filtered = $this->filterByMetaFileIndex($header, $type);
        $headerBuild = $this->buildHeaderUsingShortNames($filtered, $type);
        $this->checkForIdentifierColumn($type);

        return $headerBuild;
    }

    /**
     * Test header and row count are equal for combine
     *
     * @param $row
     * @throws \Exception
     */
    public function testHeaderRowCount($row)
    {
        if (count($this->header) !== count($row))
        {
            throw new \Exception(trans('errors.csv_row_count', [
                'headers' => count($this->header),
                'rows'    => count($row)
            ]));
        }

    }

    /**
     * Filters the array by matching meta file index with key so number of columns match.
     *
     * @param $row
     * @param $type
     * @return array
     */
    public function filterByMetaFileIndex($row, $type)
    {
        return array_intersect_key($row, $this->metaFields[$type]);
    }

    /**
     * Build header from csv file so it matches qualified short names
     *
     * @param $row
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function buildHeaderUsingShortNames($row, $type)
    {
        $header = [];
        foreach ($this->metaFields[$type] as $key => $qualified)
        {
            $header = $this->createShortNameForHeader($row, $key, $qualified, $header);
        }

        return $header;
    }

    /**
     * Create a short name for header
     *
     * @param $row
     * @param $key
     * @param $qualified
     * @param $header
     * @return mixed
     * @throws \Exception
     */
    public function createShortNameForHeader($row, $key, $qualified, $header)
    {
        if ( ! isset($row[$key]))
        {
            throw new \Exception(trans('errors.csv_build_header', ['key' => $key, 'qualified' => $qualified]));
        }

        $short = $this->checkProperty($qualified, $row[$key]);
        $header[$key] = $short;

        return $header;
    }

    /**
     * Check property for correct short name
     *
     * @param $qualified
     * @param $ns_short
     * @return string
     */
    public function checkProperty($qualified, $ns_short)
    {
        if ($qualified === 'id' || $qualified === 'coreid')
        {
            return $qualified;
        }

        list($namespace, $short) = $this->splitNameSpaceShort($ns_short);

        $short = $this->setShortNameForQualifiedName($qualified, $short, $namespace);

        return $short;
    }

    /**
     * Splits given namespace into namespace and short name
     *
     * @param $ns_short
     * @return array
     */
    protected function splitNameSpaceShort($ns_short)
    {
        list($namespace, $short) = preg_match('/:/', $ns_short) ? explode(':', $ns_short) : ['', $ns_short];

        return [$namespace, $short];
    }

    /**
     * Sets the short name value for qualified names for easier use in headers. Also prevents duplicate short names
     * with difference qualified names.
     *
     * If $checkQualified, then short name exists and used.
     * If $checkQualified is null and $checkShort exists, then create new short combined with random string.
     * If neither exist, create new qualified and short name.
     *
     * @param $qualified
     * @param $short
     * @param $namespace
     * @return string
     */
    protected function setShortNameForQualifiedName($qualified, $short, $namespace)
    {
        $checkQualified = $this->propertyContract->findBy('qualified', $qualified);

        $checkShort = $this->propertyContract->findBy('short', $short);

        if ($checkQualified !== null)
        {
            $short = $checkQualified->short;
        }
        elseif ($checkQualified === null && $checkShort !== null)
        {
            $short .= md5(str_random(4));
            $this->saveProperty($qualified, $short, $namespace);
        }
        elseif ($checkQualified === null && $checkShort === null)
        {
            $this->saveProperty($qualified, $short, $namespace);
        }

        return $short;
    }

    /**
     * Save qualified and short name to Property table
     *
     * @param $qualified
     * @param $short
     * @param $namespace
     */
    protected function saveProperty($qualified, $short, $namespace)
    {
        $array = [
            'qualified' => $qualified,
            'short'     => $short,
            'namespace' => $namespace,
        ];
        $this->propertyContract->create($array);
    }

    /**
     * Set the identifier column
     *
     * @param $type
     * @throws \Exception
     */
    public function checkForIdentifierColumn($type)
    {
        // Dealing with occurrence so return.
        if ( ! $this->mediaIsCore && $type === 'core')
        {
            return;
        }

        if (collect($this->metaFields[$type])->intersect($this->identifiers)->isEmpty())
        {
            throw new \Exception(trans('errors.missing_identifier', ['identifiers' => implode(',', $this->identifiers)]));
        }
    }

    /**
     * Set the unique id for each record.
     *
     * @param $row
     * @param $metaFields
     * @return mixed
     */
    public function setUniqueId($row, $metaFields)
    {
        return trim($this->identifierColumn) ?
            $this->getIdentifierValue($row[$this->identifierColumn])
            : $this->getIdentifierColumn($row, $metaFields);
    }

    /**
     * Get the identifier column we are using.
     *
     * @param $row
     * @param $metaFields
     * @return mixed|null
     */
    public function getIdentifierColumn($row, $metaFields)
    {
        $this->identifierColumn = collect($metaFields)->intersect($this->identifiers)
            ->filter(function ($identifier, $key) use ($row) {
                return strpos($row[$this->header[$key]], 'http') === false;
            })
            ->map(function ($identifier, $key) {
                return $this->header[$key];
            })->first();

        return trim($this->identifierColumn) ? $this->getIdentifierValue($row[$this->identifierColumn]) : null;
    }

    /**
     * If identifier is a uuid, strip the namespace. Otherwise return value.
     *
     * @param $value
     * @return mixed
     */
    public function getIdentifierValue($value)
    {
        $pattern = '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';
        return preg_match($pattern, $value, $matches) ? $matches[0] : $value;
    }

    /**
     * Works under the assumption Occurrence is the core, not Media.
     *
     * @param $row
     * @param $metaFields
     */
    public function prepareSubject($row, $metaFields)
    {
        $occurrenceId = $this->mediaIsCore ? null : $row[$this->header[0]];
        $row['id'] = $this->mediaIsCore ? $row[$this->header[0]] : $this->setUniqueId($row, $metaFields);

        if ($this->reject($row))
        {
            return;
        }

        $fields = ['project_id' => (int) $this->projectId, 'ocr' => '', 'expedition_ids' => []];

        $occurrence = is_null($occurrenceId) ? [] : ['occurrence' => ['id' => (string) $occurrenceId]];
        $subject = $fields + $row + $occurrence;

        if ($this->validateDoc($subject))
        {
            return;
        }

        $this->saveSubject($subject);
    }

    /**
     * Build subject and save to database.
     *
     * @param $subject
     */
    public function saveSubject($subject)
    {
        $this->subjectContract->create($subject);
        $this->subjectCount++;
    }

    /**
     * Save Occurrence
     *
     * @param $row
     */
    public function saveOccurrence($row)
    {
        $subjects = $this->subjectContract->getSubjectsByProjectOccurrence($this->projectId, $row[$this->header[0]]);

        $subjects->each(function ($subject) use ($row) {
            $occurrence = new Occurrence($row);
            $subject->occurrence()->save($occurrence);
        });
    }

    /**
     * Add to rejected media if subject id is not determined
     *
     * @param $row
     * @return bool
     */
    public function reject($row)
    {
        if ( ! trim($row['id']))
        {
            $this->rejectedMultimedia[] = $row;

            return true;
        }

        return false;
    }

    /**
     * Validate if subject exists using project_id and id
     * Validator->fails() returns true if validation fails
     *
     * @param $subject
     * @return bool
     */
    public function validateDoc($subject)
    {
        $rules = ['project_id' => 'unique_with:subjects,id'];
        $values = ['project_id' => $subject['project_id'], 'id' => $subject['id']];

        $validator = $this->factory->make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        $fail = $validator->fails();

        if ($fail)
        {
            $this->unsetSubjectVariables($subject);
            $this->duplicateArray[] = $subject;
        }

        return $fail;
    }

    /**
     * Unset unnecessary variables when creating csv
     *
     * @param $subject
     */
    public function unsetSubjectVariables(&$subject)
    {
        unset($subject['project_id'], $subject['ocr'], $subject['expedition_ids'], $subject['occurrence']);
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
     * Set header array and update/save
     *
     * @param $header
     * @param $loadMedia
     * @internal param $type
     */
    public function saveHeaderArray($header, $loadMedia)
    {
        $type = $loadMedia ? 'image' : 'occurrence';

        $result = $this->headerContract->findBy('project_id', $this->projectId);

        if (empty($result))
        {
            $insert = [
                'project_id' => $this->projectId,
                'header'     => [$type => $header],
            ];
            $this->headerContract->create($insert);
        }
        else
        {
            $existingHeader = $result->header;
            $existingHeader[$type] = isset($existingHeader[$type]) ?
                $this->combineHeader($existingHeader[$type], $header) : array_unique($header);
            $result->header = $existingHeader;
            $this->headerContract->update($result->toArray(), $result->id);
        }

    }

    /**
     * Combine saved header with new header
     *
     * @param $resHeader
     * @param $newHeader
     * @return array
     */
    public function combineHeader($resHeader, $newHeader)
    {
        return array_unique(array_merge($resHeader, array_diff($newHeader, $resHeader)));
    }
}