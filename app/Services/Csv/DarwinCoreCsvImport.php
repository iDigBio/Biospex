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

use App\Models\Property;
use App\Services\Models\HeaderModelService;
use App\Services\Models\SubjectModelService;
use App\Services\MongoDbService;
use Carbon\Carbon;
use Exception;
use ForceUTF8\Encoding;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Validation\Rule;
use MongoDB\BSON\ObjectId;

/**
 * Class DarwinCoreCsvImport
 */
class DarwinCoreCsvImport
{
    /**
     * Array for meta file fields: core and extension
     */
    public array $metaFields;

    /**
     * Whether media is core or extension in meta file
     */
    public bool $mediaIsCore;

    /**
     * Type: core or extension
     */
    public string $type;

    /**
     * Id of project
     */
    public int $projectId;

    /**
     * Rejected multimedia array
     */
    public array $rejectedMultimedia = [];

    /**
     * Duplicate images array
     */
    public array $duplicateArray = [];

    /**
     * @var array
     */
    public $identifiers;

    /**
     * @var int
     */
    public $subjectCount = 0;

    public $header;

    /**
     * Construct
     */
    public function __construct(
        private readonly Property $property,
        private readonly SubjectModelService $subjectModelService,
        private readonly HeaderModelService $headerModelService,
        private readonly Validation $factory,
        private readonly Csv $csv,
        private readonly MongoDbService $mongoDbService
    ) {
        $this->identifiers = config('config.dwcRequiredFields.extension.identifier');
        $this->mongoDbService->setCollection('subjects');
    }

    /**
     * Set meta properties ascertained in dwc and needed for processing csv file
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
     * @throws \Exception
     */
    public function processCsvHeader($header, $type): array
    {
        $filtered = $this->filterByMetaFileIndex($header, $type);
        $headerBuild = $this->buildHeaderUsingShortNames($filtered, $type);
        $this->checkForIdentifierColumn($type);

        return $headerBuild;
    }

    /**
     * Test header and row count are equal for combine
     *
     * @throws \Exception
     */
    public function testHeaderRowCount($row)
    {
        if (count($this->header) !== count($row)) {
            throw new Exception(t('Header column count does not match row count. :headers headers / :rows rows', [
                ':headers' => count($this->header),
                ':rows' => count($row),
            ]));
        }
    }

    /**
     * Filters the array by matching meta file index with key so number of columns match.
     *
     * @return array
     */
    public function filterByMetaFileIndex($row, $type)
    {
        return array_intersect_key($row, $this->metaFields[$type]);
    }

    /**
     * Build header from csv file so it matches qualified short names
     *
     * @throws \Exception
     */
    public function buildHeaderUsingShortNames($row, $type): array
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
     */
    protected function splitNameSpaceShort(string $ns_short): string
    {
        [$namespace, $short] = str_contains($ns_short, ':') ? explode(':', $ns_short) : ['', $ns_short];

        return $short;
    }

    /**
     * Save short name property to database.
     *
     * @return void
     */
    protected function setShortName(string $short)
    {
        $checkShort = $this->property->where('short', $short)->first();
        if ($checkShort === null) {
            $this->property->create(['short' => $short]);
        }
    }

    /**
     * Save qualified and short name to Property table
     */
    protected function saveProperty($qualified, $short, $namespace)
    {
        $array = [
            'qualified' => $qualified,
            'short' => $short,
            'namespace' => $namespace,
        ];
        $this->property->create($array);
    }

    /**
     * Set the identifier column
     *
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
            })->map(function ($identifier, $key) use ($row) {
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
     * @return mixed
     */
    public function checkIdentifierUuid($value)
    {
        $pattern = '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';

        return preg_match($pattern, $value, $matches) ? $matches[0] : $value;
    }

    /**
     * Works under the assumption Occurrence is the core, not Media.
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
     */
    public function saveSubject($subject)
    {
        // Was causing subject not to be inserted in mongodb due to text index on ocr.
        if (isset($subject['language'])) {
            unset($subject['language']);
        }

        $this->subjectModelService->create($subject);
        $this->subjectCount++;
    }

    /**
     * Save Occurrence
     */
    public function saveOccurrence($row)
    {
        $row['_id'] = new ObjectId;
        $row['updated_at'] = Carbon::now();
        $row['created_at'] = Carbon::now();

        $criteria = ['project_id' => (int) $this->projectId, 'occurrence.id' => $row[$this->header[0]]];
        $attributes = ['$set' => ['occurrence' => $row]];
        $this->mongoDbService->updateMany($attributes, $criteria);
    }

    /**
     * Add to rejected media if subject id is not determined
     */
    public function reject($row): bool
    {
        $this->rejectedMultimedia[] = $row;

        return true;
    }

    /**
     * Validate if subject exists using project_id and id
     * Validator->fails() returns true if validation fails
     *
     * @return bool
     */
    public function validateDoc($subject)
    {
        $rules = ['project_id' => Rule::unique('mongodb.subjects')->where(function ($query) use ($subject) {
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
     * @internal param $type
     */
    public function saveHeaderArray($header, $loadMedia)
    {
        $type = $loadMedia ? 'image' : 'occurrence';

        $result = $this->headerModelService->getFirst('project_id', $this->projectId);

        if (empty($result)) {
            $insert = [
                'project_id' => $this->projectId,
                'header' => [$type => $header],
            ];
            $this->headerModelService->create($insert);
        } else {
            $existingHeader = $result->header;
            $existingHeader[$type] = isset($existingHeader[$type]) ? $this->combineHeader($existingHeader[$type], $header) : array_unique($header);
            $result->header = $existingHeader;
            $result->save();
        }
    }

    /**
     * Combine saved header with new header
     *
     * @return array
     */
    public function combineHeader($resHeader, $newHeader)
    {
        return array_unique(array_merge($resHeader, array_diff($newHeader, $resHeader)));
    }
}
