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
use App\Models\Expedition;
use App\Models\ExpeditionStat;
use App\Models\Subject;
use App\Services\Csv\Csv as CsvService;
use Illuminate\Console\Command;

/**
 * Class AppSubjectReplace
 *
 * Reads storage/app/export.csv, expects a header column "subjectId".
 * Retrieves expedition by id to find its project_id. Then iterates all subjects
 * in that project; if a subject's subjectId is NOT present in the CSV list,
 * removes the expedition id from the subject's expedition_ids array in MongoDB.
 */
class AppSubjectReplace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * expeditionId: The expedition id to remove from subjects NOT in CSV
     * --dry-run: If set, no database modifications will be performed, only a report will be shown
     * --path: Optional path relative to storage/app (default: export.csv)
     */
    protected $signature = 'app:subject-replace
                            {expeditionId : Expedition id to disassociate from subjects NOT in CSV}
                            {--dry-run : Run without saving changes}
                            {--path=export.csv : CSV path relative to storage/app}';

    /** @var string */
    protected $description = 'For subjects in the expedition\'s project that are NOT listed by subjectId in the CSV, remove the expedition id from their expedition_ids array.';

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

        \Log::info('Starting AppSubjectReplace');
        \Log::info("CSV: {$csvPath}");
        \Log::info('Expedition ID: '.(string) $expeditionId);
        \Log::info('Mode: '.($dryRun ? 'DRY RUN (no changes will be saved)' : 'LIVE (changes will be saved)'));
        $this->newLine();

        // Load expedition and project id
        /** @var Expedition|null $expedition */
        $expedition = null;
        try {
            $expedition = Expedition::query()->where('_id', $expeditionId)->orWhere('id', $expeditionId)->first();
        } catch (\Throwable $e) {
            // fallback simple find
        }
        if (! $expedition) {
            try {
                $expedition = Expedition::query()->find($expeditionId);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if (! $expedition) {
            $this->error('Expedition not found for id: '.(string) $expeditionId);

            return 1;
        }
        $projectId = $expedition->project_id ?? $expedition->projectId ?? null;
        if ($projectId === null) {
            $this->error('Expedition found but project_id is missing.');

            return 1;
        }
        \Log::info('Project ID: '.(string) $projectId);

        // Stats
        $stats = [
            'csv_rows_total' => 0,
            'csv_rows_with_subjectId' => 0,
            'csv_subjectIds_count' => 0,
            'subjects_scanned' => 0,
            'expedition_ids_already_absent' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        // Read CSV and collect subjectIds
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
        $subjectIdSet = [];

        foreach ($records as $row) {
            $stats['csv_rows_total']++;

            // normalize keys
            if (! array_key_exists('subjectId', $row)) {
                $normalizedRow = [];
                foreach ($row as $k => $v) {
                    $normalizedRow[trim((string) $k)] = $v;
                }
                $row = $normalizedRow;
            }
            if (! isset($row['subjectId']) || $row['subjectId'] === '') {
                continue;
            }
            $stats['csv_rows_with_subjectId']++;
            $sidRaw = $row['subjectId'];
            $sidStr = trim((string) $sidRaw);
            if ($sidStr === '') {
                continue;
            }
            // Store exactly as string to match model primary keys (e.g., Mongo ObjectId)
            $subjectIdSet[$sidStr] = true;
        }
        $stats['csv_subjectIds_count'] = count($subjectIdSet);

        \Log::info('Unique subjectIds loaded from CSV: '.$stats['csv_subjectIds_count']);
        $this->newLine();
        \Log::info('Scanning project subjects...');

        // Iterate subjects in the project
        $query = Subject::query()->where('project_id', $projectId);

        // If expedition id type matters in Mongo, keep both string/int forms for comparison
        $expeditionIdInt = is_numeric($expeditionId) ? (int) $expeditionId : null;
        $expeditionIdStr = (string) $expeditionId;

        try {
            foreach ($query->cursor() as $subject) {
                $stats['subjects_scanned']++;

                // Determine subject identifier as the model primary key (id)
                $sid = method_exists($subject, 'getKey') ? $subject->getKey() : null;
                if ($sid === null) {
                    // Fallbacks in case getKey is unavailable
                    $sid = $subject->id ?? ($subject->_id ?? null);
                }

                $sidKey = $sid !== null ? (string) $sid : null;
                $inCsv = $sidKey !== null && isset($subjectIdSet[$sidKey]);

                // If subject is in CSV, we keep its expedition association; skip
                if ($inCsv) {
                    continue;
                }

                // Not in CSV: ensure expedition id is removed from this subject
                try {
                    $expeditionIds = (array) ($subject->expedition_ids ?? []);
                    $contains = in_array($expeditionIdStr, $expeditionIds, true)
                        || ($expeditionIdInt !== null && in_array($expeditionIdInt, $expeditionIds, true));

                    if (! $contains) {
                        $stats['expedition_ids_already_absent']++;

                        continue;
                    }

                    if ($dryRun) {
                        $stats['updated']++;

                        continue;
                    }

                    // Prefer atomic pull operations
                    $subject->pull('expedition_ids', $expeditionIdStr);
                    if ($expeditionIdInt !== null) {
                        $subject->pull('expedition_ids', $expeditionIdInt);
                    }
                    $stats['updated']++;
                } catch (\Throwable $e) {
                    // Fallback to manual save
                    try {
                        $current = (array) ($subject->expedition_ids ?? []);
                        $new = array_values(array_filter($current, function ($val) use ($expeditionIdStr, $expeditionIdInt) {
                            if ($val === $expeditionIdStr) {
                                return false;
                            }
                            if ($expeditionIdInt !== null && $val === $expeditionIdInt) {
                                return false;
                            }
                            if ((string) $val === $expeditionIdStr) {
                                return false;
                            }
                            if ($expeditionIdInt !== null && (int) $val === $expeditionIdInt) {
                                return false;
                            }

                            return true;
                        }));
                        $subject->expedition_ids = $new;
                        $subject->save();
                        $stats['updated']++;
                    } catch (\Throwable $e2) {
                        $stats['errors']++;
                        $this->error('Failed updating subjectId='.(string) $sid.' : '.$e2->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->error('Failed iterating project subjects: '.$e->getMessage());

            return 1;
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
        \Log::info('Completed AppSubjectReplace');
        \Log::info('Summary:');
        \Log::info('  CSV total rows:                 '.$stats['csv_rows_total']);
        \Log::info('  CSV rows with subjectId:        '.$stats['csv_rows_with_subjectId']);
        \Log::info('  Unique CSV subjectIds:          '.$stats['csv_subjectIds_count']);
        \Log::info('  Project subjects scanned:       '.$stats['subjects_scanned']);
        \Log::info('  Already absent association:     '.$stats['expedition_ids_already_absent']);
        \Log::info('  '.($dryRun ? 'Would update (dry run):      ' : 'Updated:                       ').$stats['updated']);
        \Log::info('  Errors:                         '.$stats['errors']);

        if (! $dryRun && $stats['updated'] > 0) {
            \Log::info('  ActorExpedition records updated: '.$actorExpeditionUpdated);
            \Log::info('  ExpeditionStat records updated:  '.$expeditionStatUpdated);
        }

        return 0;
    }
}
