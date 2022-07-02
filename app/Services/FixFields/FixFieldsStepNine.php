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

class FixFieldsStepNine extends FixFieldsBase
{
    /**
     * Start.
     *
     * @return void
     */
    public function start()
    {
        \Artisan::call('lada-cache:flush');
        \Artisan::call('lada-cache:disable');

        $this->process();

        \Artisan::call('lada-cache:enable');
    }

    /**
     * Start process.
     *
     * @return void
     */
    public function process()
    {
        $properties = collect($this->getPropertiesFile('step5-dupMixed-properties.json'));

        $mappedOccurrenceFieldsToProjects = $this->mapFieldsToProjectId($properties, 'occurrenceHeaderProjectIds');
        $this->writeToFile('step9-occurrencefields-properties.json', $mappedOccurrenceFieldsToProjects);

        $fieldsOne = [];
        $matched = [];
        $fieldsTwo = [];

        $mappedOccurrenceFieldsToProjects->each(function($array, $id) use(&$fieldsOne, &$matched, &$fieldsTwo) {
            $matched[$id] = $array['matched'];
            $fieldsOne[$id] = ['recordID1fd02e92c4bd9b40ed8041b690de4bb3' => 'recordID'];
            $fieldsTwo[$id] = ['recordIDce2dbfd038a66c3b7aa7a8e4a56fc1ac' => 'recordID'];
        });

        $type = 'occurrence';
        collect($matched)->each(function ($fields, $projectId) use ($type){
            $this->updateFields($projectId, $fields, $type);
        });

        collect($fieldsOne)->each(function ($fields, $projectId) use ($type){
            $this->updateFields($projectId, $fields, $type);
        });

        collect($fieldsTwo)->each(function ($fields, $projectId) use ($type){
            $this->updateFields($projectId, $fields, $type);
        });
    }
}