<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\FixFields;

use App\Models\Property;
use App\Services\MongoDbService;
use App\Services\Project\HeaderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FixFieldsBase
{
    /**
     * FixFieldsBase constructor.
     */
    public function __construct(
        public MongoDbService $mongoDbService,
        public HeaderService $headerService,
        public Property $property
    ) {}

    /**
     * Write contents to file.
     *
     * @return void
     */
    public function writeToFile(string $fileName, Collection $content)
    {
        Storage::put($fileName, $content->toJson());
    }

    /**
     * Get properties file.
     */
    public function getPropertiesFile(string $filename): array
    {
        return json_decode(Storage::get($filename), true);
    }

    /**
     * Set good name for updating.
     */
    public function setGoodName(string $fieldName): string
    {
        $fields = [
            'associatedSequences' => 'associatedsequences',
            'collID' => 'collId',
            'recordID' => 'recordId',
        ];

        if (($newFieldName = array_search($fieldName, $fields)) !== false) {
            return $newFieldName;
        }

        return $fieldName;
    }

    /**
     * Update fields.
     *
     * @return void
     */
    public function updateFields(string $projectId, array $fields, string $type)
    {
        if (empty($fields)) {
            return;
        }

        $this->updateMongoFields($projectId, $fields, $type);
        $this->removeAndSetHeader($projectId, $fields, $type);
        $this->removeAndSetProperty($fields);
    }

    /**
     * Update multiple fields by renaming.
     */
    public function updateMongoFields(string $id, array $fields, string $type): bool
    {
        $this->mongoDbService->setCollection('subjects');
        $criteria = ['project_id' => (int) $id];

        $renameFields = collect($fields)->mapWithKeys(function ($newField, $oldField) use ($type) {
            return $type === 'image' ?
                [$oldField => $newField] :
                ['occurrence.'.$oldField => 'occurrence.'.$newField];
        })->toArray();

        $attributes = ['$rename' => $renameFields];

        return $this->mongoDbService->updateMany($criteria, $attributes)->isAcknowledged();
    }

    /**
     * Remove bad field name from header, add to header if it doesn't exist.
     *
     * @return void
     */
    public function removeAndSetHeader(string $projectId, array $fields, string $type)
    {
        $record = $this->headerService->getFirst('project_id', $projectId);

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
     * @return void
     */
    public function removeAndSetProperty(array $fields)
    {
        collect($fields)->each(function ($newFieldName, $oldFieldName) {
            $record = $this->property->where('short', $oldFieldName)->first();
            $record?->delete();

            $result = $this->property->where('short', $newFieldName)->first();
            if ($result === null) {
                $this->property->create(['short' => $newFieldName]);
            }
        });
    }

    /**
     * Map image fields to project ids.
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
     * @return void
     */
    public function stringComparison(&$array, $setField, $oldField)
    {
        if (strcasecmp($setField, $oldField) !== 0) {
            $array = array_merge($array, [$oldField => $setField]);

            return;
        }

        if ($setField !== $oldField) {
            ! isset($array['matched']) ?
                $array['matched'] = [$oldField => $setField] :
                $array['matched'] = array_merge($array['matched'], [$oldField => $setField]);
        }
    }
}
