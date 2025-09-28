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

use App\Models\Subject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class DeleteMultimediaSubjectsCommand
 */
class DeleteMultimediaSubjectsCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:delete-multimedia-subjects';

    /**
     * The console command description.
     */
    protected $description = 'Delete subjects with project ID 13 using identifiers from multimedia.csv';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $csvPath = storage_path('multimedia.csv');

        if (! File::exists($csvPath)) {
            $this->error("File not found: {$csvPath}");

            return;
        }

        $this->info('Processing multimedia.csv file...');

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->error("Could not open file: {$csvPath}");

            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error('Could not read CSV header');
            fclose($handle);

            return;
        }

        // Find the identifier column
        $identifierColumn = array_search('identifier', $header);
        if ($identifierColumn === false) {
            $this->error('Identifier column not found in CSV');
            fclose($handle);

            return;
        }

        $deletedCount = 0;
        $processedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $processedCount++;

            if (! isset($row[$identifierColumn]) || empty($row[$identifierColumn])) {
                $this->warn("Row {$processedCount}: Empty identifier, skipping");

                continue;
            }

            $identifier = $row[$identifierColumn];

            // Delete subjects with project_id 13 and the identifier from CSV
            $deletedRecords = Subject::where('project_id', 13)
                ->where('identifier', $identifier)
                ->delete();

            if ($deletedRecords > 0) {
                $deletedCount += $deletedRecords;
                $this->info("Deleted {$deletedRecords} record(s) for identifier: {$identifier}");
            }
        }

        fclose($handle);

        $this->info('Processing complete!');
        $this->info("Processed {$processedCount} rows from CSV");
        $this->info("Total records deleted: {$deletedCount}");
    }
}
