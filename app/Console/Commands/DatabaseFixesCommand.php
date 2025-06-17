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

namespace App\Console\Commands;

use App\Services\FixFields\FixFieldsStepEight;
use App\Services\FixFields\FixFieldsStepFive;
use App\Services\FixFields\FixFieldsStepFour;
use App\Services\FixFields\FixFieldsStepNine;
use App\Services\FixFields\FixFieldsStepOne;
use App\Services\FixFields\FixFieldsStepSeven;
use App\Services\FixFields\FixFieldsStepSix;
use App\Services\FixFields\FixFieldsStepThree;
use App\Services\FixFields\FixFieldsStepTwo;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DatabaseFixesCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'data:fix {step}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    private FixFieldsStepOne $fixFieldsStepOne;

    private FixFieldsStepTwo $fixFieldsStepTwo;

    private FixFieldsStepThree $fixFieldsStepThree;

    private FixFieldsStepFour $fixFieldsStepFour;

    private FixFieldsStepFive $fixFieldsStepFive;

    private FixFieldsStepSix $fixFieldsStepSix;

    private FixFieldsStepSeven $fixFieldsStepSeven;

    private FixFieldsStepEight $fixFieldsStepEight;

    private FixFieldsStepNine $fixFieldsStepNine;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(
        FixFieldsStepOne $fixFieldsStepOne,
        FixFieldsStepTwo $fixFieldsStepTwo,
        FixFieldsStepThree $fixFieldsStepThree,
        FixFieldsStepFour $fixFieldsStepFour,
        FixFieldsStepFive $fixFieldsStepFive,
        FixFieldsStepSix $fixFieldsStepSix,
        FixFieldsStepSeven $fixFieldsStepSeven,
        FixFieldsStepEight $fixFieldsStepEight,
        FixFieldsStepNine $fixFieldsStepNine
    ) {
        parent::__construct();

        $this->fixFieldsStepOne = $fixFieldsStepOne;
        $this->fixFieldsStepTwo = $fixFieldsStepTwo;
        $this->fixFieldsStepThree = $fixFieldsStepThree;
        $this->fixFieldsStepFour = $fixFieldsStepFour;
        $this->fixFieldsStepFive = $fixFieldsStepFive;
        $this->fixFieldsStepSix = $fixFieldsStepSix;
        $this->fixFieldsStepSeven = $fixFieldsStepSeven;
        $this->fixFieldsStepEight = $fixFieldsStepEight;
        $this->fixFieldsStepNine = $fixFieldsStepNine;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        // Step db: run db queries
        if ($this->argument('step') === 'db') {
            \DB::statement('ALTER TABLE properties CHANGE short short VARCHAR(255) BINARY NOT NULL;');
            \DB::statement('ALTER TABLE `properties` DROP INDEX `properties_qualified_unique`');
            \DB::statement('ALTER TABLE `properties`DROP `qualified`');
            \DB::statement('ALTER TABLE `properties`DROP `namespace`');

            return;
        }

        // Step 1: generate properties with counts
        if ($this->argument('step') === '1') {
            $this->fixFieldsStepOne->start();

            return;
        }

        // Step 2: Removed unused fields from header array in headers table
        if ($this->argument('step') === '2') {
            $this->fixFieldsStepTwo->start();

            return;
        }

        // Step 3: Remove property fields that have no values from properties table
        if ($this->argument('step') === '3') {
            $this->fixFieldsStepThree->start();

            return;
        }

        // Step 4: remove empty fields
        if ($this->argument('step') === '4') {
            $this->fixFieldsStepFour->start();

            return;
        }

        // Step 5: Check if field & alternate fields exist together in same record
        if ($this->argument('step') === '5') {
            $this->fixFieldsStepFive->start();

            return;
        }

        // Step 6: Fix dup image subjects
        if ($this->argument('step') === '6') {
            $this->fixFieldsStepSix->start();

            return;
        }

        // Step 7: Fix dup occurrence subjects
        if ($this->argument('step') === '7') {
            $this->fixFieldsStepSeven->start();

            return;
        }

        // Step 8: Fix dup mixed subjects
        if ($this->argument('step') === '8') {
            $this->fixFieldsStepEight->start();

            return;
        }

        // Step 9: Fix dup mixed occurrence subjects
        if ($this->argument('step') === '9') {
            $this->fixFieldsStepNine->start();
        }
    }
}
