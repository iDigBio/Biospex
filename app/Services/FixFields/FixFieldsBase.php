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

namespace App\Services\FixFields;

use App\Repositories\HeaderRepository;
use App\Repositories\PropertyRepository;
use App\Services\MongoDbService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FixFieldsBase
{
    /**
     * @var \App\Services\MongoDbService
     */
    public MongoDbService $mongoDbService;

    /**
     * @var \App\Repositories\HeaderRepository
     */
    public HeaderRepository $headerRepository;

    /**
     * @var \App\Repositories\PropertyRepository
     */
    public PropertyRepository $propertyRepository;

    /**
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Repositories\HeaderRepository $headerRepository
     * @param \App\Repositories\PropertyRepository $propertyRepository
     */
    public function __construct(
        MongoDbService $mongoDbService,
        HeaderRepository $headerRepository,
        PropertyRepository $propertyRepository
    ) {

        $this->mongoDbService = $mongoDbService;
        $this->headerRepository = $headerRepository;
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * Write contents to file.
     *
     * @param string $fileName
     * @param \Illuminate\Support\Collection $content
     * @return void
     */
    public function writeToFile(string $fileName, Collection $content)
    {
        Storage::put($fileName, $content->toJson());
    }

    /**
     * Get properties file.
     *
     * @param string $filename
     * @return array
     */
    public function getPropertiesFile(string $filename): array
    {
        return json_decode(Storage::get($filename), true);
    }

    /**
     * Set good name for updating.
     *
     * @param string $fieldName
     * @return string
     */
    public function setGoodName(string $fieldName): string
    {
        $fields = [
            'associatedSequences' => 'associatedsequences',
            'collID'              => 'collId',
            'recordID'            => 'recordId',
        ];

        if (($newFieldName = array_search($fieldName, $fields)) !== false) {
            return $newFieldName;
        }
        return $fieldName;
    }

    /**
     * Update fields.
     *
     * @param array $fields
     * @param string $projectId
     * @param string $type
     * @return void
     */
    public function updateFields(string $projectId, array $fields, string $type)
    {
        if (empty($fields)) return;

        $this->updateMongoFields($projectId, $fields, $type);
        $this->removeAndSetHeader($projectId, $fields, $type);
        $this->removeAndSetProperty($fields);
    }

    /**
     * Update multiple fields by renaming.
     *
     * @param string $id
     * @param array $fields
     * @param string $type
     * @return bool
     */
    public function updateMongoFields(string $id, array $fields, string $type): bool
    {
        $this->mongoDbService->setCollection('subjects');
        $criteria = ['project_id' => (int) $id];

        $renameFields = collect($fields)->mapWithKeys(function ($newField, $oldField) use($type) {
            return $type === 'image' ?
                [$oldField => $newField] :
                ['occurrence.'.$oldField => 'occurrence.'.$newField];
        })->toArray();

        $attributes = ['$rename' => $renameFields];

        return $this->mongoDbService->updateMany($attributes, $criteria)->isAcknowledged();
    }

    /**
     * Remove bad field name from header, add to header if it doesn't exist.
     *
     * @param string $projectId
     * @param array $fields
     * @param string $type
     * @return void
     */
    public function removeAndSetHeader(string $projectId, array $fields, string $type)
    {
        $record = $this->headerRepository->findBy('project_id', $projectId);

        if ($record !== null) {
            $header = $record->header;
            collect($fields)->each(function ($newField, $oldField) use (&$header, $type) {
                if (($badIndex = array_search($oldField, $header[$type])) !== false) {
                    unset($header[$type][$badIndex]);
                }

                if (! in_array($newField, $header[$type])) {
                    $header[$type][] = $newField;
                }
            });

            $record->header = $header;

            $record->save();
        }
    }

    /**
     * Remove bad name and create new if it does not exist.
     *
     * @param array $fields
     * @return void
     */
    public function removeAndSetProperty(array $fields)
    {
        collect($fields)->each(function ($newFieldName, $oldFieldName) {
            $record = $this->propertyRepository->findBy('short', $oldFieldName);
            $record?->delete();

            $result = $this->propertyRepository->findBy('short', $newFieldName);
            if ($result === null) {
                $this->propertyRepository->create(['short' => $newFieldName]);
            }
        });
    }

    /**
     * Map image fields to project ids.
     *
     * @param \Illuminate\Support\Collection $properties
     * @param string $headerType
     * @return \Illuminate\Support\Collection
     */
    public function mapFieldsToProjectId(Collection $properties, string $headerType): Collection
    {
        $mappedProjectIds = collect();

        $properties->each(function ($property) use (&$mappedProjectIds, $headerType) {
            $mappedProjectIds = $mappedProjectIds->merge(collect($property[$headerType]))->unique();
            collect($property['fields'])->each(function ($object) use (&$mappedProjectIds, $headerType) {
                $mappedProjectIds = $mappedProjectIds->merge(collect($object[$headerType]))->unique();
            });
        });

        return $mappedProjectIds->mapWithKeys(function ($id) use ($properties, $headerType) {
            $array = [];
            $properties->each(function ($property, $field) use ($id, &$array, $headerType) {
                $setField = $this->setGoodName($field);
                if (in_array($id, $property[$headerType])) {
                    $this->stringComparison($array, $setField, $field);
                }
                collect($property['fields'])->each(function ($object, $oldField) use (
                    &$array,
                    $id,
                    $setField,
                    $headerType
                ) {
                    if (in_array($id, $object[$headerType])) {
                        $this->stringComparison($array, $setField, $oldField);
                    }
                });
            });

            return [$id => $array];
        });
    }

    /**
     * Compares strings. If matched but different case, set matched.
     *
     * @param $array
     * @param $setField
     * @param $oldField
     * @return void
     */
    public function stringComparison(&$array, $setField, $oldField)
    {
        if (strcasecmp($setField, $oldField) !== 0) {
            $array = array_merge($array, [$oldField => $setField]);
            return;
        }

        if ($setField !== $oldField) {
            !isset($array['matched']) ?
                $array['matched'] = [$oldField => $setField] :
                $array['matched'] = array_merge($array['matched'], [$oldField => $setField]);
        }
    }
}