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

class FixFieldsStepFour extends FixFieldsBase
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

        $this->removePropertiesWithEmptyFields();

        \Artisan::call('lada-cache:enable');
    }

    /**
     * Step 4: Remove properties that no longer have alternate fields.
     *
     * @return void
     */
    private function removePropertiesWithEmptyFields()
    {
        $properties = collect($this->getPropertiesFile('step3-properties.json'));

        $filtered = $properties->reject(function ($property, $key) {
            return empty($property['fields']);
        });

        $this->writeToFile('step4-properties.json', $filtered);
    }
}
