<?php

/*
 * Copyright (C) 2014 - 2026, Biospex
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

use App\Models\Expedition;
use App\Models\Subject;
use App\Services\DarwinCore\DwcValidationService;
use Illuminate\Console\Command;

class AppClearSubjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-subjects {uuid : The UUID of the expedition} {--dry-run : Display the count without updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the OCR data from every subject assigned to an expedition';

    /**
     * Execute the console command.
     */
    public function handle(DwcValidationService $validationService): int
    {
        $uuid = $this->argument('uuid');

        if (! $validationService->isValidUuid($uuid)) {
            $this->error('Invalid UUID format provided.');

            return self::FAILURE;
        }

        $expedition = Expedition::where('uuid', $uuid)->first();

        if (! $expedition) {
            $this->error("Expedition with UUID {$uuid} not found.");

            return self::FAILURE;
        }

        // MongoDB is type sensitive; expedition_ids contains integers in this app
        $expeditionId = (int) $expedition->id;

        $query = Subject::where('expedition_ids', $expeditionId);
        $subjectCount = $query->count();

        if ($subjectCount === 0) {
            $this->info('No subjects found for this expedition.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$subjectCount} subjects would have their OCR cleared.");

            return self::SUCCESS;
        }

        if (! $this->confirm("Are you sure you want to clear OCR for {$subjectCount} subjects in '{$expedition->title}'?")) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $this->info("Clearing OCR for {$subjectCount} subjects...");

        /**
         * Using the Subject model (mongodb/laravel-mongodb) to perform a bulk update.
         * The 'where' clause on an array field like 'expedition_ids' in MongoDB
         * automatically checks for existence of the value.
         */
        $query->update(['ocr' => '']);

        $this->info('OCR data cleared successfully.');

        return self::SUCCESS;
    }
}
