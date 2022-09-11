<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Csv;

use App\Repositories\HeaderRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\SubjectRepository;
use App\Services\MongoDbService;
use Carbon\Carbon;
use Exception;
use ForceUTF8\Encoding;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Validation\Rule;
use MongoDB\BSON\ObjectId;

/**
 * Class DarwinCoreCsvImport
 *
 * @package App\Services\Csv
 */
class DarwinCoreCsvImport
{
    /**
     * @var \App\Repositories\PropertyRepository
     */
    public $propertyRepo;

    /**
     * @var \App\Repositories\SubjectRepository
     */
    public $subjectRepo;

    /**
     * @var \App\Repositories\HeaderRepository
     */
    public $headerRepo;

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
    public $rejectedMultimedia = [];

    /**
     * Duplicate images array
     *
     * @var array
     */
    public $duplicateArray = [];

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
    public $header;

    /**
     * @var MongoDbService
     */
    private $mongoDbService;

    /**
     * Construct
     *
     * @param \App\Repositories\PropertyRepository $propertyRepo
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Repositories\HeaderRepository $headerRepo
     * @param Validation $factory
     * @param \App\Services\Csv\Csv $csv
     * @param MongoDbService $mongoDbService
     */
    public function __construct(
        PropertyRepository $propertyRepo,
        SubjectRepository $subjectRepo,
        HeaderRepository $headerRepo,
        Validation $factory,
        Csv $csv,
        MongoDbService $mongoDbService
    ) {
        $this->identifiers = config('config.dwcRequiredFields.extension.identifier');
        $this->propertyRepo = $propertyRepo;
        $this->subjectRepo = $subjectRepo;
        $this->headerRepo = $headerRepo;
        $this->factory = $factory;
        $this->csv = $csv;
        $this->mongoDbService = $mongoDbService;
        $this->mongoDbService->setCollection('subjects');
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
        foreach ($rows as $offset => $row) {
            $this->processRow($row, $type, $loadMedia);
        }

        $this->saveHeaderArray($this->header, $loadMedia);

        unset($rows);
        unset($this->header);
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

        array_walk($row, function (&$value) {
            $value = Encoding::toUTF8($value);
        });

        $loadMedia ? $this->prepareSubject($row, $this->metaFields[$type]) : $this->saveOccurrence($row);
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
        if (count($this->header) !== count($row)) {
            throw new Exception(t('Header column count does not match row count. :headers headers / :rows rows', [
                ':headers' => count($this->header),
                ':rows'    => count($row),
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
        foreach ($this->metaFields[$type] as $key => $qualified) {
            $this->createShortNameForHeader($row, $key, $qualified, $header);
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
    public function createShortNameForHeader($row, $key, $qualified, &$header)
    {
        if (! isset($row[$key])) {
            throw new Exception(t('Undefined index for :key => :qualified when building header for csv import.', [':key' => $key, ':qualified' => $qualified]));
        }

        $short = $this->checkProperty($qualified, $row[$key], $header);
        $header[$key] = $short;
    }

    /**
     * Check property for correct short name
     *
     * @param $qualified
     * @param $ns_short
     * @param $header
     * @return string
     */
    public function checkProperty($qualified, $ns_short, &$header): string
    {
        if ($qualified === 'id' || $qualified === 'coreid') {
            return $qualified;
        }

        $short = $this->splitNameSpaceShort($ns_short);
        if (in_array(trim($short), $header)) {
            $short = $ns_short;
        }

        $this->setShortName($short);

        return trim($short);
    }

    /**
     * Splits given namespace into namespace and short name
     *
     * @param string $ns_short
     * @return string
     */
    protected function splitNameSpaceShort(string $ns_short): string
    {
        [$namespace, $short] = str_contains($ns_short, ':') ? explode(':', $ns_short) : ['', $ns_short];

        return $short;
    }

    /**
     * Save short name property to database.
     *
     * @param string $short
     * @return void
     */
    protected function setShortName(string $short)
    {
        $checkShort = $this->propertyRepo->findBy('short', $short);
        if ($checkShort === null) {
            $this->propertyRepo->create(['short' => $short]);
        }
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
        $this->propertyRepo->create($array);
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
        if (! $this->mediaIsCore && $type === 'core') {
            return;
        }

        if (collect($this->metaFields[$type])->intersect($this->identifiers)->isEmpty()) {

            $error = t('The Darwin Core Archive is missing the required identifier column inside the csv file. Accepted identifiers: %s', implode(',', $this->identifiers));

            throw new Exception($error);
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
        $identifierValue = $this->getIdentifierValue($row, $metaFields);

        return $identifierValue === false ? false : $this->checkIdentifierUuid($identifierValue);
    }

    /**
     * Get the identifier column we are using.
     *
     * @param $row
     * @param $metaFields
     * @return bool
     */
    public function getIdentifierValue($row, $metaFields)
    {
        $identifierColumnValues = collect($metaFields)
            ->intersect($this->identifiers)
            ->filter(function ($identifier, $key) use ($row) {
                if (isset($row[$this->header[$key]])
                    && ! empty($row[$this->header[$key]])
                    && (! str_contains($row[$this->header[$key]], 'http'))) {
                    return true;
                }

                return false;
            })->map(function($identifier, $key) use ($row) {
                return $row[$this->header[$key]];
            });

        if ($identifierColumnValues->isEmpty()) {
            $rejected = ['Reason' => t('All identifier columns empty or identifier is URL.')] + $row;
            $this->reject($rejected);

            return false;
        }

        return $identifierColumnValues->first();
    }

    /**
     * If identifier is a uuid, strip the namespace. Otherwise return value.
     *
     * @param $value
     * @return mixed
     */
    public function checkIdentifierUuid($value)
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

        if ($this->checkColumns($row)) {
            return;
        }

        $fields = ['project_id' => (int) $this->projectId, 'ocr' => '', 'expedition_ids' => [], 'exported' => false];

        $occurrence = is_null($occurrenceId) ? [] : ['occurrence' => ['id' => (string) $occurrenceId]];
        $subject = $fields + $row + $occurrence;

        if ($this->validateDoc($subject)) {
            return;
        }

        $this->saveSubject($subject);
    }

    /**
     * Check if id and accessURI exists.
     *
     * @param $row
     * @return bool
     */
    private function checkColumns($row)
    {
        if (! trim($row['id'])) {
            $rejected = ['Reason' => t('Missing required ID value.')] + $row;
            $this->reject($rejected);

            return true;
        }

        if (empty($row['accessURI'])) {
            $rejected = ['Reason' => t('Missing accessURI.')] + $row;
            $this->reject($rejected);

            return true;
        }

        return false;
    }

    /**
     * Build subject and save to database.
     *
     * @param $subject
     */
    public function saveSubject($subject)
    {
        // Was causing subject not to be inserted in mongodb due to text index on ocr.
        if (isset($subject['language'])) {
            unset($subject['language']);
        }

        $this->subjectRepo->create($subject);
        $this->subjectCount++;
    }

    /**
     * Save Occurrence
     *
     * @param $row
     */
    public function saveOccurrence($row)
    {
        $row['_id'] = new ObjectId();
        $row['updated_at'] = Carbon::now();
        $row['created_at'] = Carbon::now();

        $criteria = ['project_id' => (int) $this->projectId, 'occurrence.id' => $row[$this->header[0]]];
        $attributes = ['$set' => ['occurrence' => $row]];
        $this->mongoDbService->updateMany($attributes, $criteria);
    }

    /**
     * Add to rejected media if subject id is not determined
     *
     * @param $row
     * @return bool
     */
    public function reject($row)
    {
        $this->rejectedMultimedia[] = $row;

        return true;
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
        $rules = ['project_id' => Rule::unique('mongodb.subjects')->where(function ($query) use($subject) {
            return $query->where('project_id', $subject['project_id'])->where('id', $subject['id']);
        })];

        $validator = $this->factory->make($subject, $rules);

        $fail = $validator->fails();

        if ($fail) {
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

        $result = $this->headerRepo->findBy('project_id', $this->projectId);

        if (empty($result)) {
            $insert = [
                'project_id' => $this->projectId,
                'header'     => [$type => $header],
            ];
            $this->headerRepo->create($insert);
        } else {
            $existingHeader = $result->header;
            $existingHeader[$type] = isset($existingHeader[$type]) ? $this->combineHeader($existingHeader[$type], $header) : array_unique($header);
            $result->header = $existingHeader;
            $this->headerRepo->update($result->toArray(), $result->id);
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