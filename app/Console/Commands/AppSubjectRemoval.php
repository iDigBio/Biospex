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

use App\Models\ActorExpedition;
use App\Models\ExpeditionStat;
use App\Models\Subject;
use App\Services\Csv\Csv as CsvService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

// for phpstan hints only

/**
 * Class AppSubjectRemoval
 *
 * Reads storage/app/export.csv, expects a header column "subjectId".
 * For each subjectId, removes the provided expedition id from the
 * subject's expedition_ids array in MongoDB.
 */
class AppSubjectRemoval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * expeditionId: The expedition id to remove from each subject's expedition_ids array
     * --dry-run: If set, no database modifications will be performed, only a report will be shown
     * --path: Optional path relative to storage/app (default: export.csv)
     */
    protected $signature = 'app:subject-removal
                            {expeditionId : Expedition id to disassociate}
                            {--dry-run : Run without saving changes}
                            {--path=export.csv : CSV path relative to storage/app}';

    /** @var string */
    protected $description = 'Remove an expedition id from subjects listed by subjectId in storage/app/export.csv';

    /**
     * @throws \League\Csv\Exception
     */
    public function handle(): int
    {
        $expeditionIdInput = $this->argument('expeditionId');
        $expeditionId = is_numeric($expeditionIdInput) ? (int) $expeditionIdInput : $expeditionIdInput;
        $dryRun = (bool) $this->option('dry-run');
        $relativePath = $this->option('path') ?: 'export.csv';

        // Resolve CSV full path within storage/app
        $csvPath = storage_path('app'.DIRECTORY_SEPARATOR.$relativePath);

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found at: {$csvPath}");

            return 1;
        }

        \Log::info('Starting AppSubjectRemoval');
        \Log::info("CSV: {$csvPath}");
        \Log::info('Expedition ID: '.(string) $expeditionId);
        \Log::info('Mode: '.($dryRun ? 'DRY RUN (no changes will be saved)' : 'LIVE (changes will be saved)'));
        $this->newLine();

        // Stats
        $stats = [
            'rows_total' => 0,
            'rows_with_subjectId' => 0,
            'subjects_found' => 0,
            'subjects_missing' => 0,
            'already_absent' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        $csv = new CsvService;
        try {
            $csv->readerCreateFromPath($csvPath);
            $csv->setHeaderOffset(0); // first row is header
        } catch (\Throwable $e) {
            $this->error('Failed to open CSV: '.$e->getMessage());

            return 1;
        }

        try {
            $header = $csv->getHeader();
            if (! in_array('subjectId', $header, true)) {
                $this->error('CSV is missing required header column: subjectId');

                return 1;
            }
        } catch (\Throwable $e) {
            $this->error('Failed to read CSV header: '.$e->getMessage());

            return 1;
        }

        $records = $csv->getRecords(); // iterator of associative arrays

        foreach ($records as $row) {
            $stats['rows_total']++;

            // Defensive normalization of header keys
            // Ensure we can access using exact header name
            if (! array_key_exists('subjectId', $row)) {
                // Some CSVs can have BOM or spacing; attempt normalized keys
                $normalizedRow = [];
                foreach ($row as $k => $v) {
                    $normalizedRow[trim((string) $k)] = $v;
                }
                $row = $normalizedRow;
            }

            if (! isset($row['subjectId']) || $row['subjectId'] === '') {
                continue; // skip rows without subjectId
            }

            $stats['rows_with_subjectId']++;
            $subjectId = is_numeric($row['subjectId']) ? (int) $row['subjectId'] : $row['subjectId'];

            try {
                /** @var Subject|null $subject */
                $subject = Subject::subjectId($subjectId)->first();

                if (! $subject) {
                    $stats['subjects_missing']++;
                    $this->warn("Subject not found for subjectId={$subjectId}");

                    continue;
                }

                $stats['subjects_found']++;

                $expeditionIds = (array) ($subject->expedition_ids ?? []);
                $contains = in_array($expeditionId, $expeditionIds, true) || in_array((int) $expeditionId, $expeditionIds, true);

                if (! $contains) {
                    $stats['already_absent']++;

                    continue;
                }

                if ($dryRun) {
                    // No write, just count
                    $stats['updated']++;

                    continue;
                }

                // Perform update: pull the expedition id from the array
                try {
                    // Prefer atomic pull when supported by mongodb/laravel-mongodb
                    $subject->pull('expedition_ids', $expeditionId);
                    // In case expedition ids are stored as integers and input as string or vice versa, ensure both types removed
                    if (is_int($expeditionId)) {
                        $subject->pull('expedition_ids', (string) $expeditionId);
                    } else {
                        $subject->pull('expedition_ids', (int) $expeditionId);
                    }
                    $stats['updated']++;
                } catch (\Throwable $e) {
                    // Fallback to manual array update if pull is not available
                    try {
                        $current = (array) ($subject->expedition_ids ?? []);
                        $new = array_values(array_filter($current, function ($val) use ($expeditionId) {
                            return $val !== $expeditionId && $val !== (int) $expeditionId && $val !== (string) $expeditionId;
                        }));
                        $subject->expedition_ids = $new;
                        $subject->save();
                        $stats['updated']++;
                    } catch (\Throwable $e2) {
                        $stats['errors']++;
                        $this->error("Failed to update subjectId={$subjectId}: ".$e2->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->error('Error processing row #'.$stats['rows_total'].': '.$e->getMessage());
            }
        }

        // After processing all subjects, update expedition counts
        $this->newLine();
        \Log::info('Updating expedition counts...');

        $actorExpeditionUpdated = 0;
        $expeditionStatUpdated = 0;

        if (! $dryRun && $stats['updated'] > 0) {
            try {
                // Count remaining subjects in the expedition
                $remainingSubjectCount = Subject::where('expedition_ids', $expeditionId)->count();
                \Log::info("Remaining subjects in expedition {$expeditionId}: {$remainingSubjectCount}");

                // Update ActorExpedition records
                $actorExpeditionUpdated = ActorExpedition::where('expedition_id', $expeditionId)
                    ->update(['total' => $remainingSubjectCount]);

                // Update ExpeditionStat record
                $expeditionStatUpdated = ExpeditionStat::where('expedition_id', $expeditionId)
                    ->update(['local_subject_count' => $remainingSubjectCount]);

            } catch (\Throwable $e) {
                $this->error('Failed to update expedition counts: '.$e->getMessage());
            }
        }

        $this->newLine();
        \Log::info('Completed AppSubjectRemoval');
        \Log::info('Summary:');
        \Log::info('  Total CSV rows:            '.$stats['rows_total']);
        \Log::info('  Rows with subjectId:       '.$stats['rows_with_subjectId']);
        \Log::info('  Subjects found:            '.$stats['subjects_found']);
        \Log::info('  Subjects missing:          '.$stats['subjects_missing']);
        \Log::info('  Already absent association: '.$stats['already_absent']);
        \Log::info('  '.($dryRun ? 'Would update (dry run):   ' : 'Updated:                  ').$stats['updated']);
        \Log::info('  Errors:                    '.$stats['errors']);

        if (! $dryRun && $stats['updated'] > 0) {
            \Log::info('  ActorExpedition records updated: '.$actorExpeditionUpdated);
            \Log::info('  ExpeditionStat records updated:  '.$expeditionStatUpdated);
        }

        return 0;
    }
}
