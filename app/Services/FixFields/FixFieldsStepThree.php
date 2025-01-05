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

class FixFieldsStepThree extends FixFieldsBase
{
    /**
     * Start process.
     *
     * @return void
     */
    public function start()
    {
        echo 'Starting to remove unused fields on properties'.PHP_EOL;

        \Artisan::call('lada-cache:flush');
        \Artisan::call('lada-cache:disable');

        $this->removeUnusedFieldDataFromProperties();

        \Artisan::call('lada-cache:enable');
    }

    /**
     * Step 3: remove unused fields data from properties table
     *
     * @return void
     */
    private function removeUnusedFieldDataFromProperties()
    {
        $properties = collect($this->getPropertiesFile('step2-properties.json'));

        $propertiesRefreshed = $properties->mapWithKeys(function ($property, $key) {
            foreach ($property['fields'] as $field => $object) {
                if (empty($object['imageHeaderIds']) && empty($object['occurrenceHeaderIds'])) {
                    $record = $this->property->where('short', $field)->first();
                    $record?->delete();
                    unset($property['fields'][$field]);
                }
            }

            return [$key => $property];
        });

        $this->writeToFile('step3-properties.json', $propertiesRefreshed);
    }
}
