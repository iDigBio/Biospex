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

class FixFieldsStepTwo extends FixFieldsBase
{
    /**
     * Start process.
     *
     * @return void
     */
    public function start()
    {
        echo "Starting to zero headers in properties." . PHP_EOL;

        \Artisan::call('lada-cache:flush');
        \Artisan::call('lada-cache:disable');

        $this->fixZeroData();

        \Artisan::call('lada-cache:enable');
    }

    /**
     * Step 2: Zero and unset project headers where fields don't exist in mongodb.
     *
     * @return void
     */
    private function fixZeroData()
    {
        $properties = collect($this->getPropertiesFile('step1-properties.json'));

        $mappedProperties = $properties->map(function ($property, $key) {
            return $this->fixImageZeroHeaders($property, $key);
        })->map(function ($property, $key){
            return $this->fixOccurrenceZeroHeaders($property, $key);
        });

        $this->writeToFile('step2-properties.json', $mappedProperties);
    }

    /**
     * Step 2: Set correct image header if field not used.
     *
     * @param $property
     * @param $key
     * @return mixed
     */
    private function fixImageZeroHeaders($property, $key): mixed
    {
        if ($property['imageFieldCount'] === 0 && $property['imageHeaderCount'] > 0) {
            $imageHeaderProjectIds = $property['imageHeaderProjectIds'];
            $imageHeaderIds = $property['imageHeaderIds'];
            foreach ($imageHeaderIds as $index => $id) {
                $project_id = $this->updateHeader($id, $key, 'image');
                if (($idKey = array_search($project_id, $imageHeaderProjectIds)) !== false) {
                    unset($imageHeaderProjectIds[$idKey]);
                }
                unset($imageHeaderIds[$index]);
            }
            $property['imageHeaderCount'] = count($imageHeaderIds);
            $property['imageHeaderIds'] = $imageHeaderIds;
            $property['imageHeaderProjectIds'] = $imageHeaderProjectIds;
        }

        return $this->fixFieldImageZeroHeaders($property);
    }

    /**
     * Step 2: remove occurrence headers where not used.
     *
     * @param $property
     * @param $key
     * @return mixed
     */
    private function fixOccurrenceZeroHeaders($property, $key): mixed
    {
        if ($property['imageFieldCount'] === 0 && $property['imageHeaderCount'] > 0) {
            $occurrenceHeaderProjectIds = $property['occurrenceHeaderProjectIds'];
            $occurrenceHeaderIds = $property['occurrenceHeaderIds'];
            foreach ($occurrenceHeaderIds as $index => $id) {
                $project_id = $this->updateHeader($id, $key,'occurrence');
                if (($idKey = array_search($project_id, $occurrenceHeaderProjectIds)) !== false) {
                    unset($occurrenceHeaderProjectIds[$idKey]);
                }
                unset($occurrenceHeaderIds[$index]);
            }
            $property['occurrenceHeaderCount'] = count($occurrenceHeaderIds);
            $property['occurrenceHeaderIds'] = $occurrenceHeaderIds;
            $property['occurrenceHeaderProjectIds'] = $occurrenceHeaderProjectIds;
        }

        return $this->fixFieldOccurrenceZeroHeaders($property);
    }

    /**
     * Step 2: fix field image headers where not used.
     *
     * @param $property
     * @return mixed
     */
    private function fixFieldImageZeroHeaders($property): mixed
    {
        foreach ($property['fields'] as $field => $object) {
            if ($object['imageFieldCount'] === 0 && $object['imageHeaderCount'] > 0) {
                $imageHeaderProjectIds = $object['imageHeaderProjectIds'];
                $imageHeaderIds = $object['imageHeaderIds'];
                foreach ($imageHeaderIds as $index => $id) {
                    $project_id = $this->updateHeader($id, $field, 'image');
                    if (($idKey = array_search($project_id, $imageHeaderProjectIds)) !== false) {
                        unset($imageHeaderProjectIds[$idKey]);
                    }
                    unset($imageHeaderIds[$index]);
                }
                $property['fields'][$field]['imageHeaderCount'] = count($imageHeaderIds);
                $property['fields'][$field]['imageHeaderIds'] = $imageHeaderIds;
                $property['fields'][$field]['imageHeaderProjectIds'] = $imageHeaderProjectIds;
            }
        }

        return $property;
    }

    /**
     * Step 2: fix occurrence fields headers where not used.
     *
     * @param $property
     * @return mixed
     */
    private function fixFieldOccurrenceZeroHeaders($property): mixed
    {
        foreach ($property['fields'] as $field => $object) {
            if ($object['occurrenceFieldCount'] === 0 && $object['occurrenceHeaderCount'] > 0) {
                $occurrenceHeaderProjectIds = $object['occurrenceHeaderProjectIds'];
                $occurrenceHeaderIds = $object['occurrenceHeaderIds'];
                foreach ($occurrenceHeaderIds as $index => $id) {
                    $project_id = $this->updateHeader($id, $field, 'occurrence');
                    if (($idKey = array_search($project_id, $occurrenceHeaderProjectIds)) !== false) {
                        unset($occurrenceHeaderProjectIds[$idKey]);
                    }
                    unset($occurrenceHeaderIds[$index]);
                }
                $property['fields'][$field]['occurrenceHeaderCount'] = count($occurrenceHeaderIds);
                $property['fields'][$field]['occurrenceHeaderIds'] = $occurrenceHeaderIds;
                $property['fields'][$field]['occurrenceHeaderProjectIds'] = $occurrenceHeaderProjectIds;
            }
        }

        return $property;
    }

    /**
     * find record and update.
     *
     * @param string $id
     * @param mixed $key
     * @param string $type
     * @return null
     */
    private function updateHeader(string $id, mixed $key, string $type)
    {
        $record = $this->headerModelService->findWithRelations($id);
        if ($record !== null) {
            $header = $record->header;
            foreach ($header[$type] as $int => $value) {
                if ($value === $key) {
                    unset($header[$type][$int]);
                }
            }
            $record->header = $header;
            $record->save();
        }

        return $record->project_id ?? null;
    }
}