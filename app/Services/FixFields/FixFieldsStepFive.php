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

class FixFieldsStepFive extends FixFieldsBase
{

    /**
     * Start process.
     *
     * @return void
     */
    public function start()
    {
        \Artisan::call('lada-cache:flush');
        \Artisan::call('lada-cache:disable');

        $this->checkDuplicateFieldsExist();

        \Artisan::call('lada-cache:enable');
    }

    /**
     * Step 5: Check if field & alternate fields exist together in same record
     */
    private function checkDuplicateFieldsExist()
    {
        $properties = collect($this->getPropertiesFile('step4-properties.json'));

        $images = [];
        $occurrences = [];
        $mixed = [];

        $properties->each(function ($property, $key) use(&$images, &$occurrences, &$mixed) {
            foreach ($property['fields'] as $field => $array) {
                if (($property['imageFieldCount'] > 0 && $array['occurrenceFieldCount'] > 0)
                    || ($property['occurrenceFieldCount'] > 0 && $array['imageFieldCount'] > 0)) {
                    $mixed[$key] = $property;

                    continue;
                }

                if ($property['imageFieldCount'] > 0 || $array['imageFieldCount'] > 0) {
                    $images[$key] = $property;
                    continue;
                }

                $occurrences[$key] = $property;

            }
        });

        $dupImages = $this->checkImageDupes($images);
        $this->writeToFile('step5-dupImages-properties.json', $dupImages);

        $dupOccurrences = $this->checkOccurrenceDupes($occurrences);
        $this->writeToFile('step5-dupOccurrences-properties.json', $dupOccurrences);

        $dupMixed = $this->checkMixedDups($mixed);
        $this->writeToFile('step5-dupMixed-properties.json', $dupMixed);
    }

    /**
     * Step 5: Check dup fields in same record for images.
     *
     * @param $images
     * @return \Illuminate\Support\Collection
     */
    private function checkImageDupes($images): \Illuminate\Support\Collection
    {
        return collect($images)->map(function($property, $key) {
            $first = collect($property['imageHeaderProjectIds']);
            foreach ($property['fields'] as $index => $array) {
                $second = collect($property['fields'][$index]['imageHeaderProjectIds']);
                $projectIds = $first->merge($second)->unique();
                $duplicates = $projectIds->reject(function($id){
                    return empty($id);
                })->map(function($id) use ($key, $index){
                    return $this->countFieldDuplication($id, $key, $index);
                })->reject(function($id){
                    return $id === 0;
                });

                if ($duplicates->count() > 0) {
                    dd($property);
                }

                $property['doubleImageIds'] = collect(array_merge($property['doubleImageIds'], $duplicates->toArray()))->unique()->toArray();
            }

            return $property;
        });
    }

    /**
     * Step 5: Check dup fields in same record.
     *
     * @param $occurrences
     * @return \Illuminate\Support\Collection
     */
    private function checkOccurrenceDupes($occurrences): \Illuminate\Support\Collection
    {
        return collect($occurrences)->each(function($property, $key) {
            $first = collect($property['occurrenceHeaderProjectIds']);
            foreach ($property['fields'] as $index => $array) {
                $second = collect($property['fields'][$index]['occurrenceHeaderProjectIds']);
                $projectIds = $first->merge($second)->unique();
                $duplicates = $projectIds->reject(function($id){
                    return empty($id);
                })->map(function($id) use ($key, $index){
                    return $this->countFieldDuplication($id, $key, $index);
                })->reject(function($id){
                    return $id === 0;
                });

                if ($duplicates->count() > 0) {
                    dd($property);
                }

                $property['doubleOccurrenceIds'] = collect(array_merge($property['doubleOccurrenceIds'], $duplicates->toArray()))->unique()->toArray();
            }

            return $property;
        });
    }

    /**
     * Step 5: Check mixed for dup fields.
     *
     * @param $mixed
     * @return \Illuminate\Support\Collection
     */
    private function checkMixedDups($mixed): \Illuminate\Support\Collection
    {
        $dupImages = $this->checkImageDupes($mixed);

        return $this->checkOccurrenceDupes($dupImages);
    }

    /**
     * Count fields that exist in same record.
     *
     * @param int $projectId
     * @param string $fieldOne
     * @param string $fieldTwo
     * @return int
     */
    public function countFieldDuplication(int $projectId, string $fieldOne, string $fieldTwo): int
    {
        $this->mongoDbService->setCollection('subjects');

        return $this->mongoDbService->count([
            'project_id' => $projectId,
            $fieldOne => ['$exists' => true],
            $fieldTwo => ['$exists' => true]
        ]);
    }
}