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

class FixFieldsStepSeven extends FixFieldsBase
{
    /**
     * Start
     *
     * @return void
     */
    public function start()
    {
        echo 'Starting to match dup occurrence and update fields to project id.'.PHP_EOL;

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
        $properties = collect($this->getPropertiesFile('step5-dupOccurrences-properties.json'));

        $mappedFieldsToProjects = $this->mapFieldsToProjectId($properties, 'occurrenceHeaderProjectIds');

        $this->writeToFile('step7-dupOccurrences-properties.json', $mappedFieldsToProjects);

        $mappedFieldsToProjects->each(function ($fields, $projectId) {
            $matched = $fields['matched'] ?? [];
            $this->updateFields($projectId, $matched, 'occurrence');

            unset($fields['matched']);

            $this->updateFields($projectId, $fields, 'occurrence');
        });
    }
}
