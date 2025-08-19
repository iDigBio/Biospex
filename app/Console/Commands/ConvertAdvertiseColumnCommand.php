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

use App\Services\Helpers\GeneralService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertAdvertiseColumnCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-advertise-column {--dry-run : Preview changes without executing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert advertise column from LONGBLOB to JSON format with UTF-8 cleaning';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting conversion of advertise column from LONGBLOB to JSON...');

        // Check current column type
        $columns = Schema::getColumnListing('projects');
        if (! in_array('advertise', $columns)) {
            $this->error('Column "advertise" does not exist in projects table');

            return 1;
        }

        // Get projects with advertise data
        $projects = DB::table('projects')
            ->whereNotNull('advertise')
            ->where('advertise', '!=', '')
            ->get();

        $this->info("Found {$projects->count()} projects with advertise data");

        if ($projects->isEmpty()) {
            $this->info('No projects with advertise data found. Proceeding with column type change...');
        } else {
            // Process each project
            $successCount = 0;
            $errorCount = 0;

            foreach ($projects as $project) {
                try {
                    // Unserialize the current data
                    $advertiseData = unserialize($project->advertise);

                    if ($advertiseData === false) {
                        $this->warn("Project ID {$project->id}: Failed to unserialize advertise data");
                        $errorCount++;

                        continue;
                    }

                    // Clean UTF-8 encoding for all string values in the array
                    $cleanedData = $this->cleanUtf8InArray($advertiseData);

                    // Convert to JSON
                    $jsonData = json_encode($cleanedData, JSON_UNESCAPED_UNICODE);

                    if ($jsonData === false) {
                        $this->warn("Project ID {$project->id}: Failed to encode data as JSON");
                        $errorCount++;

                        continue;
                    }

                    if (! $isDryRun) {
                        // Update the record with JSON data
                        DB::table('projects')
                            ->where('id', $project->id)
                            ->update(['advertise' => $jsonData]);
                    }

                    $this->line("Project ID {$project->id}: Converted successfully");
                    $successCount++;

                } catch (\Exception $e) {
                    $this->error("Project ID {$project->id}: Error - ".$e->getMessage());
                    $errorCount++;
                }
            }

            $this->info("Conversion completed: {$successCount} successful, {$errorCount} errors");
        }

        if (! $isDryRun) {
            // Validate all advertise data is proper JSON before changing column type
            $this->info('Validating all advertise data is proper JSON...');
            $invalidRecords = $this->validateAllJsonData();

            if ($invalidRecords > 0) {
                $this->error("Found {$invalidRecords} records with invalid JSON data. Cannot proceed with column conversion.");
                $this->error('Please run the command again to fix remaining data issues.');

                return 1;
            }

            // Change column type from binary to JSON
            $this->info('Converting column type from LONGBLOB to JSON...');

            try {
                // Use a safer approach: create new column, copy data, drop old, rename
                DB::statement('ALTER TABLE projects ADD COLUMN advertise_json JSON');
                DB::statement('UPDATE projects SET advertise_json = CAST(advertise AS JSON) WHERE advertise IS NOT NULL');
                DB::statement('ALTER TABLE projects DROP COLUMN advertise');
                DB::statement('ALTER TABLE projects CHANGE COLUMN advertise_json advertise JSON');
                $this->info('Column type successfully changed to JSON');
            } catch (\Exception $e) {
                $this->error('Failed to change column type: '.$e->getMessage());
                // Try to clean up if the new column was created
                try {
                    $columns = Schema::getColumnListing('projects');
                    if (in_array('advertise_json', $columns)) {
                        DB::statement('ALTER TABLE projects DROP COLUMN advertise_json');
                    }
                } catch (\Exception $cleanupException) {
                    // Ignore cleanup errors
                }

                return 1;
            }
        } else {
            $this->info('DRY RUN: Would change column type from LONGBLOB to JSON');
        }

        $this->info('Advertise column conversion completed successfully!');

        return 0;
    }

    /**
     * Recursively clean UTF-8 encoding in array values
     */
    private function cleanUtf8InArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8InArray($value);
            }
        } elseif (is_string($data)) {
            // Clean UTF-8 encoding using the existing GeneralService method
            $generalService = app(\App\Services\Helpers\GeneralService::class);
            $data = $generalService->forceUtf8($data, 'UTF-8');
        }

        return $data;
    }

    /**
     * Validate that all advertise data is proper JSON
     */
    private function validateAllJsonData()
    {
        $invalidCount = 0;

        // Get all projects with advertise data
        $projects = DB::table('projects')
            ->whereNotNull('advertise')
            ->where('advertise', '!=', '')
            ->get();

        foreach ($projects as $project) {
            // Check if the data is valid JSON
            json_decode($project->advertise);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to fix the record if it's still serialized
                try {
                    $advertiseData = unserialize($project->advertise);
                    if ($advertiseData !== false) {
                        // Clean and convert to JSON
                        $cleanedData = $this->cleanUtf8InArray($advertiseData);
                        $jsonData = json_encode($cleanedData, JSON_UNESCAPED_UNICODE);

                        if ($jsonData !== false) {
                            // Update the record
                            DB::table('projects')
                                ->where('id', $project->id)
                                ->update(['advertise' => $jsonData]);

                            $this->line("Fixed Project ID {$project->id}: Converted remaining serialized data to JSON");
                        } else {
                            $invalidCount++;
                            $this->warn("Project ID {$project->id}: Could not convert to valid JSON");
                        }
                    } else {
                        $invalidCount++;
                        $this->warn("Project ID {$project->id}: Invalid data format");
                    }
                } catch (\Exception $e) {
                    $invalidCount++;
                    $this->warn("Project ID {$project->id}: Error processing data - ".$e->getMessage());
                }
            }
        }

        return $invalidCount;
    }
}
