<?php

namespace App\Services\Csv;

use App\Exceptions\BiospexException;
use App\Exceptions\CsvHeaderCountException;
use App\Exceptions\CsvHeaderNameException;
use App\Exceptions\MissingMetaIdentifier;
use App\Repositories\Contracts\HeaderContract;
use App\Repositories\Contracts\PropertyContract;
use App\Repositories\Contracts\SubjectContract;
use ForceUTF8\Encoding;
use Illuminate\Validation\Factory as Validation;
use App\Models\Occurrence;

class DarwinCoreCsvImport
{

    /**
     * @var PropertyContract
     */
    public $propertyContract;

    /**
     * @var SubjectContract
     */
    public $subjectContract;

    /**
     * @var HeaderContract
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
     * Construct
     *
     * @param PropertyContract $propertyContract
     * @param SubjectContract $subjectContract
     * @param HeaderContract $headerContract
     * @param Validation $factory
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        PropertyContract $propertyContract,
        SubjectContract $subjectContract,
        HeaderContract $headerContract,
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
     * @throws BiospexException
     */
    public function loadCsvFile($file, $delimiter, $enclosure, $type, $loadMedia)
    {
        $this->csv->readerCreateFromPath($file);
        $this->csv->setDelimiter($delimiter);
        $this->csv->setEnclosure($enclosure);

        $header = $this->processCsvHeader($this->csv->getHeaderRow(), $type);

        $rows = $this->csv->fetch();
        foreach ($rows as $row)
        {
            if (empty($row[0]))
            {
                continue;
            }
            $this->processRow($header, $row, $type, $loadMedia);
        }

        $this->saveHeaderArray($header, $loadMedia);
    }

    /**
     * Process an individual row.
     *
     * @param $header
     * @param $row
     * @param $type
     * @param $loadMedia
     * @throws BiospexException
     * @return bool
     */
    public function processRow($header, $row, $type, $loadMedia)
    {
        $row = $this->filterByMetaFileIndex($row, $type);

        $this->testHeaderRowCount($header, $row);

        array_walk($row, function (&$value) {
            $value = Encoding::toUTF8($value);
        });

        $combined = array_combine($header, $row);

        $loadMedia ?
            $this->prepareSubject($header, $combined, $this->metaFields[$type])
            : $this->saveOccurrence($header, $combined);

        return true;
    }

    /**
     * Process a csv header
     *
     * @param $header
     * @param $type
     * @return array
     * @throws BiospexException
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
     * @param $header
     * @param $row
     * @throws CsvHeaderCountException
     */
    public function testHeaderRowCount($header, $row)
    {
        if (count($header) !== count($row))
        {
            throw new CsvHeaderCountException(trans('errors.csv_row_count', [
                'headers' => count($header),
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
     * @throws BiospexException
     * @return array
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
     * @throws CsvHeaderNameException
     */
    public function createShortNameForHeader($row, $key, $qualified, $header)
    {
        if ( ! isset($row[$key]))
        {
            throw new CsvHeaderNameException(trans('errors.csv_build_header', ['key' => $key, 'qualified' => $qualified]));
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
        $checkQualified = $this->propertyContract->setCacheLifetime(0)
            ->where('qualified', '=', $qualified)
            ->findFirst();

        $checkShort = $this->propertyContract->setCacheLifetime(0)
            ->where('short', '=', $short)
            ->findFirst();

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
     * @throws MissingMetaIdentifier
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
            throw new MissingMetaIdentifier(trans('errors.missing_identifier', ['identifiers' => implode(',', $this->identifiers)]));
        }
    }

    /**
     * Set the unique id for each record.
     *
     * @param $header
     * @param $combined
     * @param $metaFields
     * @return mixed
     */
    public function setUniqueId($header, $combined, $metaFields)
    {
        return trim($this->identifierColumn) ?
            $this->getIdentifierValue($combined[$this->identifierColumn])
            : $this->getIdentifierColumn($header, $combined, $metaFields);
    }

    /**
     * Get the identifier column we are using.
     *
     * @param $header
     * @param $combined
     * @param $metaFields
     * @return mixed|null
     */
    public function getIdentifierColumn($header, $combined, $metaFields)
    {
        $this->identifierColumn = collect($metaFields)->intersect($this->identifiers)
            ->filter(function ($identifier, $key) use ($combined, $header) {
                return strpos($combined[$header[$key]], 'http') === false;
            })
            ->map(function ($identifier, $key) use ($header) {
                return $header[$key];
            })->first();

        return trim($this->identifierColumn) ? $this->getIdentifierValue($combined[$this->identifierColumn]) : null;
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
     * Set UUID values
     * @param $combined
     * @param $type
     * @return mixed
     */
    public function setUuids(&$combined, $type)
    {
        return (isset($combined[$this->identifierColumn]) && ! empty($combined[$this->identifierColumn])) ?
            $this->uuidFromIdentifier($combined) : $this->uuidFromMetaFields($combined, $type);
    }

    /**
     * Pull UUID from identifier values or send original if not UUID format
     * @param $combined
     * @return mixed
     */
    protected function uuidFromIdentifier(&$combined)
    {
        $pattern = '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';
        return preg_match($pattern, $combined[$this->identifierColumn], $matches) ?
            $matches[0] : $combined[$this->identifierColumn];
    }

    /**
     * Pull UUID from column values or send original value if not UUID format
     * @param $combined
     * @param $type
     * @return mixed
     */
    protected function uuidFromMetaFields(&$combined, $type)
    {
        $pattern = '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';
        return preg_match($pattern, $combined[$this->metaFields[$type][0]], $matches) ?
            $matches[0] : $combined[$this->metaFields[$type][0]];
    }

    /**
     * Works under the assumption Occurrence is the core, not Media.
     *
     * @param $header
     * @param $combined
     * @param $metaFields
     */
    public function prepareSubject($header, $combined, $metaFields)
    {
        $occurrenceId = $this->mediaIsCore ? null : $combined[$header[0]];
        $combined['id'] = $this->mediaIsCore ? $combined[$header[0]] : $this->setUniqueId($header, $combined, $metaFields);

        if ($this->reject($combined))
        {
            return;
        }

        $fields = ['project_id' => (int) $this->projectId, 'ocr' => '', 'expedition_ids' => []];

        $occurrence = is_null($occurrenceId) ? [] : ['occurrence' => ['id' => $occurrenceId]];
        $subject = $fields + $combined + $occurrence;

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
     * @param $header
     * @param $data
     */
    public function saveOccurrence($header, $data)
    {
        $subjects = $this->subjectContract->setCacheLifetime(0)
            ->where('project_id', '=', $this->projectId)
            ->where('occurrence.id', '=', $data[$header[0]])
            ->findAll();

        if ($subjects->isEmpty())
        {
            return;
        }

        $subjects->each(function ($subject) use ($data) {
            $occurrence = new Occurrence($data);
            $subject->occurrence()->save($occurrence);
        });
    }

    /**
     * Add to rejected media if subject id is not determined
     *
     * @param $combined
     * @return bool
     */
    public function reject($combined)
    {
        if ( ! trim($combined['id']))
        {
            $this->rejectedMultimedia[] = $combined;

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

        $result = $this->headerContract->setCacheLifetime(0)
            ->where('project_id', '=', $this->projectId)
            ->findFirst();

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
            $this->headerContract->update($result->id, $result->toArray());
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