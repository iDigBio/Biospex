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
use App\Services\Csv\Csv;
use Exception;
use Illuminate\Console\Command;

/**
 * Class UpdateSubjectOccurrenceCommand
 */
class UpdateSubjectOccurrenceCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'subject:update-occurrence {--projectId= : Optional project ID to filter subjects}';

    /**
     * The console command description.
     */
    protected $description = 'Update occurrence field of Subject in MongoDB using multimedia.csv and occurrences.csv files';

    /**
     * @var Csv
     */
    private $csv;

    /**
     * Create a new command instance.
     */
    public function __construct(Csv $csv)
    {
        parent::__construct();
        $this->csv = $csv;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        \Log::info('Starting subject occurrence update...');

        try {
            // Read multimedia.csv to get coreid to identifier mapping
            $multimediaPath = storage_path('multimedia.csv');
            $occurrencesPath = storage_path('occurrences.csv');

            if (! file_exists($multimediaPath)) {
                $this->error("Multimedia CSV file not found: {$multimediaPath}");

                return;
            }

            if (! file_exists($occurrencesPath)) {
                $this->error("Occurrences CSV file not found: {$occurrencesPath}");

                return;
            }

            // Read multimedia.csv to build coreid to identifier mapping
            $multimediaData = $this->readMultimediaCsv($multimediaPath);
            \Log::info('Multimedia CSV read: '.count($multimediaData).' records');

            // Read occurrences.csv to get occurrence data by id
            $occurrenceData = $this->readOccurrencesCsv($occurrencesPath);
            \Log::info('Occurrences CSV read: '.count($occurrenceData).' records');

            // Process updates in batches using bulk operations
            $this->updateSubjectsInBatches($multimediaData, $occurrenceData);

            \Log::info('Subject occurrence update completed successfully!');

        } catch (Exception $e) {
            $this->error('Error updating subject occurrences: '.$e->getMessage());
        }
    }

    /**
     * Read multimedia.csv and build coreid to identifier mapping
     */
    private function readMultimediaCsv(string $filePath): array
    {
        $this->csv->readerCreateFromPath($filePath);
        $this->csv->setHeaderOffset(0);

        $records = $this->csv->getRecords();
        $multimediaData = [];

        foreach ($records as $record) {
            if (isset($record['coreid']) && isset($record['identifier'])) {
                $multimediaData[$record['coreid']] = $record['identifier'];
            }
        }

        return $multimediaData;
    }

    /**
     * Read occurrences.csv and build id to occurrence data mapping
     */
    private function readOccurrencesCsv(string $filePath): array
    {
        $this->csv->readerCreateFromPath($filePath);
        $this->csv->setHeaderOffset(0);

        $records = $this->csv->getRecords();
        $occurrenceData = [];

        foreach ($records as $record) {
            if (isset($record['id'])) {
                // Clean UTF-8 data for each field to prevent MongoDB errors
                $cleanRecord = $this->sanitizeUtf8Data($record);
                $occurrenceData[$record['id']] = $cleanRecord;
            }
        }

        return $occurrenceData;
    }

    /**
     * Sanitize UTF-8 data to prevent MongoDB encoding errors
     */
    private function sanitizeUtf8Data(array $data): array
    {
        $cleanData = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove invalid UTF-8 characters and replace with empty string or placeholder
                $cleanValue = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                // Remove any remaining problematic characters
                $cleanValue = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cleanValue);
                // Remove the replacement character � and similar
                $cleanValue = str_replace(['�', ''], '', $cleanValue);
                $cleanData[$key] = $cleanValue;
            } else {
                $cleanData[$key] = $value;
            }
        }

        return $cleanData;
    }

    /**
     * Update subjects in batches using MongoDB bulk write operations for better performance
     */
    private function updateSubjectsInBatches(array $multimediaData, array $occurrenceData): void
    {
        $updatedCount = 0;
        $batchSize = 100;
        $batch = [];

        // Get projectId option if provided
        $projectId = $this->option('projectId');

        \Log::info('Building bulk operations...');

        // Build update operations batch
        foreach ($multimediaData as $coreid => $identifier) {
            if (! isset($occurrenceData[$coreid])) {
                continue;
            }

            // Build filter criteria - use projectId first if provided, then identifier
            $filter = ['identifier' => $identifier];
            if ($projectId) {
                $filter = ['project_id' => (int) $projectId, 'identifier' => $identifier];
            }

            $batch[] = [
                'updateOne' => [
                    $filter,
                    ['$set' => ['occurrence' => $occurrenceData[$coreid]]],
                ],
            ];

            // Execute batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                try {
                    $result = Subject::raw(function ($collection) use ($batch) {
                        return $collection->bulkWrite($batch, ['ordered' => false]);
                    });
                    $updatedCount += $result->getModifiedCount();
                    \Log::info("Updated {$updatedCount} subjects...");
                    $batch = []; // Reset batch
                } catch (Exception $e) {
                    $this->error('Error in bulk write operation: '.$e->getMessage());
                    // Continue with next batch instead of failing completely
                    $batch = [];
                }
            }
        }

        // Execute remaining batch
        if (! empty($batch)) {
            try {
                $result = Subject::raw(function ($collection) use ($batch) {
                    return $collection->bulkWrite($batch, ['ordered' => false]);
                });
                $updatedCount += $result->getModifiedCount();
            } catch (Exception $e) {
                $this->error('Error in final bulk write operation: '.$e->getMessage());
            }
        }

        \Log::info("Total subjects updated: {$updatedCount}");
    }
}
