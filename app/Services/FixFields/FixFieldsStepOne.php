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

use Illuminate\Support\Collection;

class FixFieldsStepOne extends FixFieldsBase
{

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $headers;

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $properties;

    /**
     * Start process.
     *
     * @return void
     */
    public function start()
    {
        echo "Starting to set counts on properties." . PHP_EOL;

        \Artisan::call('lada-cache:flush');
        \Artisan::call('lada-cache:disable');

        $this->headers = $this->headerRepository->all();

        $this->properties = collect($this->getPropertiesFile('properties.json'));

        $propertiesWithCounts = $this->setPropertyCounts();

        $this->writeToFile('step1-properties.json', $propertiesWithCounts);

        \Artisan::call('lada-cache:enable');
    }


    /**
     * Sets the counts for properties.
     *
     * @return \Illuminate\Support\Collection
     */
    private function setPropertyCounts(): Collection
    {
        return $this->properties->mapWithKeys(function ($property, $key) {
            $property['imageFieldCount'] = $this->lookUpImageFieldCount($key);
            [$imageHeaderCount, $imageHeaderIds, $imageHeaderProjectIds] = $this->lookUpImageHeaderCount($key);
            $property['imageHeaderCount'] = $imageHeaderCount;
            $property['imageHeaderIds'] = $imageHeaderIds;
            $property['imageHeaderProjectIds'] = $imageHeaderProjectIds;


            $property['occurrenceFieldCount'] = $this->lookUpOccurrenceFieldCount($key);
            [$occurrenceHeaderCount, $occurrenceHeaderIds, $occurrenceHeaderProjectIds] = $this->lookUpOccurrenceHeaderCount($key);
            $property['occurrenceHeaderCount'] = $occurrenceHeaderCount;
            $property['occurrenceHeaderIds'] = $occurrenceHeaderIds;
            $property['occurrenceHeaderProjectIds'] = $occurrenceHeaderProjectIds;

            foreach ($property['fields'] as $fieldKey => $fieldValue) {
                $property['fields'][$fieldKey]['imageFieldCount'] = $this->lookUpImageFieldCount($fieldKey);
                [$imageHeaderCountB, $imageHeaderIdsB, $imageHeaderProjectIdsB] = $this->lookUpImageHeaderCount($fieldKey);
                $property['fields'][$fieldKey]['imageHeaderCount'] = $imageHeaderCountB;
                $property['fields'][$fieldKey]['imageHeaderIds'] = $imageHeaderIdsB;
                $property['fields'][$fieldKey]['imageHeaderProjectIds'] = $imageHeaderProjectIdsB;

                $property['fields'][$fieldKey]['occurrenceFieldCount'] = $this->lookUpOccurrenceFieldCount($fieldKey);
                [$occurrenceHeaderCountB, $occurrenceHeaderIdsB, $occurrenceHeaderProjectIdsB] = $this->lookUpOccurrenceHeaderCount($fieldKey);
                $property['fields'][$fieldKey]['occurrenceHeaderCount'] = $occurrenceHeaderCountB;
                $property['fields'][$fieldKey]['occurrenceHeaderIds'] = $occurrenceHeaderIdsB;
                $property['fields'][$fieldKey]['occurrenceHeaderProjectIds'] = $occurrenceHeaderProjectIdsB;
            }

            return [
                $key => $property,
            ];
        });
    }

    /**
     * Look up occurrence field count.
     *
     * @param string $value
     * @return int
     */
    public function lookUpOccurrenceFieldCount(string $value): int
    {
        $this->mongoDbService->setCollection('subjects');

        return $this->mongoDbService->count([
            'occurrence.'.$value => ['$exists' => true],
        ]);
    }

    /**
     * Look up count of value used in project headers.
     *
     * @param string $value
     * @return array
     */
    public function lookUpOccurrenceHeaderCount(string $value): array
    {
        $count = 0;
        $headerIds = [];
        $projectIds = [];
        $this->headers->each(function ($header) use ($value, &$count, &$headerIds, &$projectIds) {
            if (! isset($header->header['occurrence']) || ! in_array($value, $header->header['occurrence'])) {
                return true;
            }

            $count++;
            $headerIds[] = $header->id;
            $projectIds[] = $header->project_id;
        });

        return [$count, $headerIds, $projectIds];
    }

    /**
     * Look up image field count in mongodb.
     *
     * @param string $value
     * @return int
     */
    public function lookUpImageFieldCount(string $value): int
    {
        $this->mongoDbService->setCollection('subjects');

        return $this->mongoDbService->count([
            $value => ['$exists' => true],
        ]);
    }

    /**
     * Look up count of value used in project headers.
     *
     * @param string $value
     * @return array
     */
    public function lookUpImageHeaderCount(string $value): array
    {
        $count = 0;
        $headerIds = [];
        $projectIds = [];
        $this->headers->each(function ($header) use ($value, &$count, &$headerIds, &$projectIds) {
            if (! isset($header->header['image']) || ! in_array($value, $header->header['image'])) {
                return true;
            }

            $count++;
            $headerIds[] = $header->id;
            $projectIds[] = $header->project_id;
        });

        return [$count, $headerIds, $projectIds];
    }

}