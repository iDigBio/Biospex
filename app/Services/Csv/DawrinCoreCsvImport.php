<?php

namespace Biospex\Services\Csv;

use Biospex\Repositories\Contracts\Header;
use Biospex\Repositories\Contracts\Property;
use Biospex\Repositories\Contracts\Subject;
use ForceUTF8\Encoding;
use Illuminate\Config\Repository as Config;
use Illuminate\Validation\Factory as Validation;
use Biospex\Models\Occurrence;

class DarwinCoreCsvImport extends CsvAbstract {

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Property
     */
    public $property;

    /**
     * @var Subject
     */
    public $subject;

    /**
     * @var Header
     */
    public $header;

    /**
     * Identifier column for media in csv file
     *
     * @var string
     */
    public $identifierColumn;

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
     * @var mixed
     */
    public $identifiers;

    /**
     * Construct
     *
     * @param Config $config
     * @param Property $property
     * @param Subject $subject
     * @param Header $header
     * @param Validation $factory
     */
    public function __construct(
        Config $config,
        Property $property,
        Subject $subject,
        Header $header,
        Validation $factory
    )
    {
        $this->identifiers = $config->get('config.identifiers');
        $this->property = $property;
        $this->subject = $subject;
        $this->config = $config;
        $this->header = $header;
        $this->factory = $factory;
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
     */
    public function loadCsvFile($file, $delimiter, $enclosure, $type, $loadMedia)
    {
        $this->readerCreateFromPath($file, $delimiter, $enclosure);

        $header = $this->processCsvHeader($this->getHeaderRow(), $type);
        $this->saveHeaderArray($header, $loadMedia);

        $rows = $this->fetch();
        foreach ($rows as $row)
        {
            if (empty($row[0]))
            {
                continue;
            }
            $this->processRow($header, $row, $type, $loadMedia);
        }
    }

    /**
     * Process an individual row.
     *
     * @param $header
     * @param $row
     * @param $type
     * @param $loadMedia
     * @return bool
     * @throws \Exception
     */
    public function processRow($header, $row, $type, $loadMedia)
    {
        $row = $this->filterByMetaFileIndex($row, $type);

        $this->testHeaderRowCount($header, $row);

        array_walk($row, function (&$value)
        {
            $value = Encoding::toUTF8($value);
        });

        $combined = array_combine($header, $row);

        $this->setUuids($combined, $type);

        $loadMedia ? $this->saveSubject($header, $combined) : $this->saveOccurrence($header, $combined);

        return true;
    }

    /**
     * Process a csv header
     *
     * @param $header
     * @param $type
     * @return array
     */
    public function processCsvHeader($header, $type)
    {
        $filtered = $this->filterByMetaFileIndex($header, $type);
        $headerBuild = $this->buildHeaderUsingShortNames($filtered, $type);
        $this->setIdentifierColumn($headerBuild, $type);

        return $headerBuild;
    }

    /**
     * Test header and row count are equal for combine
     *
     * @param $header
     * @param $row
     * @throws \Exception
     */
    public function testHeaderRowCount($header, $row)
    {
        if (count($header) != count($row))
        {
            throw new \Exception(trans('emails.error_csv_row_count', [
                'headers' => count($header),
                'rows'    => count($row)
            ]));
        }

        return;
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
        $result = array_intersect_key($row, $this->metaFields[$type]);

        return $result;
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
            throw new \Exception(trans('emails.error_csv_build_header', ['key' => $key, 'qualified' => $qualified]));
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
        if ($qualified == 'id' || $qualified == 'coreid')
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
        list($namespace, $short) = preg_match('/:/', $ns_short) ? preg_split('/:/', $ns_short) : ['', $ns_short];

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
        $checkQualified = $this->property->findByQualified($qualified);
        $checkShort = $this->property->findByShort($short);

        if ( ! is_null($checkQualified))
        {
            $short = $checkQualified->short;
        }
        elseif (is_null($checkQualified) && ! is_null($checkShort))
        {
            $short .= substr(md5(uniqid(mt_rand(), true)), 0, 4);
            $this->saveProperty($qualified, $short, $namespace);
        }
        elseif (is_null($checkQualified) && is_null($checkShort))
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
        $this->property->create($array);
    }

    /**
     * Set the identifier column
     *
     * @param $header
     * @param $type
     */
    public function setIdentifierColumn($header, $type)
    {
        if ( ! $this->mediaIsCore && $type == 'core')
        {
            return;
        }

        $result = array_values(array_intersect($this->identifiers, $header));

        if ( ! $result)
        {
            return;
        }

        $this->identifierColumn = $result[0];

        return;
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
     * Build subject and save to database
     *
     * @param $header
     * @param $data
     */
    public function saveSubject($header, $data)
    {
        $occurrenceId = $this->mediaIsCore ? null : $data[$header[0]];
        $data['id'] = $this->mediaIsCore ? $data[$header[0]] : $data[$this->identifierColumn];

        if ($this->reject($data))
        {
            return;
        }

        $fields = ['project_id' => (int) $this->projectId, 'ocr' => '', 'expedition_ids' => []];

        $subject = $fields + $data + ['occurrence' => is_null($occurrenceId) ? '' : $occurrenceId];

        if ($this->validateDoc($subject))
        {
            return;
        }

        $subject = $this->subject->create($subject);

        if ( ! is_null($occurrenceId))
        {
            $subject->occurrence()->save(new Occurrence(['id' => $occurrenceId]));
        }
    }

    /**
     * Save Occurrence
     *
     * @param $header
     * @param $data
     */
    public function saveOccurrence($header, $data)
    {
        $subjects = $this->subject->findByProjectOccurrenceId($this->projectId, $data[$header[0]]);

        if ($subjects->isEmpty())
        {
            return;
        }

        foreach ($subjects as $subject)
        {
            $subject->occurrence()->save(new Occurrence($data));
        }

        return;
    }

    /**
     * Add to rejected media if subject id is not determined
     *
     * @param $data
     * @return bool
     */
    public function reject($data)
    {
        if (empty($data['id']))
        {
            $this->rejectedMultimedia[] = $data;

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
        unset($subject['project_id']);
        unset($subject['ocr']);
        unset($subject['expedition_ids']);
        unset($subject['occurrence']);
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

        $result = $this->header->getByProjectId($this->projectId);

        if (empty($result))
        {
            $insert = [
                'project_id' => $this->projectId,
                'header'     => [$type => $header],
            ];
            $this->header->create($insert);
        }
        else
        {
            $existingHeader = $result->header;
            $existingHeader[$type] = isset($existingHeader[$type]) ?
                $this->combineHeader($existingHeader[$type], $header) : array_unique($header);
            $result->header = $existingHeader;
            $result->save();
        }

        return;
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