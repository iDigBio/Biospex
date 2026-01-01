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

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetMigrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:reset-batch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old migrations and add new ones with batch = 1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration reset process...');

        // Step 1: Delete all migrations with batch >= 1
        $this->line('Deleting migrations with batch >= 1...');
        $deletedCount = DB::table('migrations')->where('batch', '>=', 1)->delete();
        $this->info("Deleted {$deletedCount} migration records.");

        // Step 2: Get all migration files from database/migration directory
        $migrationPath = database_path('migration');

        if (! File::exists($migrationPath)) {
            $this->error('Migration directory does not exist: '.$migrationPath);

            return 1;
        }

        $migrationFiles = File::files($migrationPath);
        $migrationFiles = collect($migrationFiles)
            ->filter(function ($file) {
                return $file->getExtension() === 'php';
            })
            ->map(function ($file) {
                return $file->getFilenameWithoutExtension();
            })
            ->sort()
            ->values();

        if ($migrationFiles->isEmpty()) {
            $this->warn('No migration files found in database/migration directory.');

            return 0;
        }

        $this->info("Found {$migrationFiles->count()} migration files.");

        // Step 3: Insert all migration files into migrations table with batch = 1
        $this->line('Adding migrations to migrations table with batch = 1...');

        $insertData = [];
        foreach ($migrationFiles as $migration) {
            $insertData[] = [
                'migration' => $migration,
                'batch' => 1,
            ];
        }

        DB::table('migrations')->insert($insertData);

        $this->info("Successfully added {$migrationFiles->count()} migrations with batch = 1.");
        $this->info('Migration reset process completed successfully!');

        return 0;
    }
}
