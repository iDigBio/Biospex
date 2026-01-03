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

use App\Jobs\PusherTranscriptionJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class UpdateQueries
 */
class AppUpdateQueriesCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:update-queries {operation? : The operation to run (create-directories, move-files, update-paths)}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $operation = $this->argument('operation');

        switch ($operation) {
            case 'create-directories':
                $this->createS3Directories();
                break;

            case 'move-files':
                $this->moveS3DirectoryFiles();
                break;

            case 'update-paths':
                $this->updateDatabasePaths();
                break;
            case 'process-classifications':
                $this->processClassifications();
                break;

            default:
                $this->createS3Directories();
                $this->moveS3DirectoryFiles();
                $this->updateDatabasePaths();
                $this->processClassifications();
        }
    }

    /**
     * Create required directories in S3 bucket
     */
    protected function createS3Directories(): void
    {
        $directories = [
            config('config.uploads.site-assets'),
            config('config.uploads.project-assets'),
        ];

        foreach ($directories as $directory) {
            try {
                if (! Storage::disk('s3')->exists($directory)) {
                    Storage::disk('s3')->makeDirectory($directory);
                }
            } catch (\Exception $e) {
                $this->error("Failed to create directory {$directory}: ".$e->getMessage());
            }
        }
    }

    /**
     * Move files between S3 directories using AWS CLI for optimal performance
     */
    protected function moveS3DirectoryFiles(): void
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');

        $migrations = [
            [
                'from' => config('config.uploads.project_resources_downloads'),
                'to' => config('config.uploads.project-assets'),
                'name' => 'project resources to project assets',
            ],
            [
                'from' => config('config.uploads.resources'),
                'to' => config('config.uploads.site-assets'),
                'name' => 'resources to site assets',
            ],
        ];

        foreach ($migrations as $migration) {
            $this->migrateS3Directory($bucket, $region, $migration['from'], $migration['to'], $migration['name']);
        }
    }

    /**
     * Migrate files from one S3 directory to another using AWS CLI
     */
    protected function migrateS3Directory(string $bucket, string $region, string $fromDir, string $toDir, string $description): void
    {
        // Check if source directory has files
        $listCommand = sprintf(
            'aws s3 ls s3://%s/%s/ --region %s --recursive',
            escapeshellarg($bucket),
            escapeshellarg($fromDir),
            escapeshellarg($region)
        );

        $output = [];
        $returnCode = 0;
        exec($listCommand.' 2>/dev/null', $output, $returnCode);

        if (empty($output)) {
            return;
        }

        $fileCount = count($output);

        // Get initial count of destination directory
        $destListCommand = sprintf(
            'aws s3 ls s3://%s/%s/ --region %s --recursive',
            escapeshellarg($bucket),
            escapeshellarg($toDir),
            escapeshellarg($region)
        );

        $destInitialOutput = [];
        $destInitialReturnCode = 0;
        exec($destListCommand.' 2>/dev/null', $destInitialOutput, $destInitialReturnCode);

        $initialDestFileCount = count($destInitialOutput);
        $expectedFinalCount = $initialDestFileCount + $fileCount;

        // Copy files to new directory
        $copyCommand = sprintf(
            'aws s3 cp s3://%s/%s/ s3://%s/%s/ --region %s --recursive',
            escapeshellarg($bucket),
            escapeshellarg($fromDir),
            escapeshellarg($bucket),
            escapeshellarg($toDir),
            escapeshellarg($region)
        );

        $copyOutput = [];
        $copyReturnCode = 0;
        exec($copyCommand.' 2>&1', $copyOutput, $copyReturnCode);

        if ($copyReturnCode !== 0) {
            $this->error('   Failed to copy files: '.implode("\n", $copyOutput));

            return;
        }

        // Verify copy operation by listing destination directory
        $verifyOutput = [];
        $verifyReturnCode = 0;
        exec($destListCommand.' 2>/dev/null', $verifyOutput, $verifyReturnCode);

        $actualFinalCount = count($verifyOutput);

        if ($actualFinalCount !== $expectedFinalCount) {
            $this->error("   Verification failed: Expected {$expectedFinalCount} files, found {$actualFinalCount}");

            return;
        }
    }

    /**
     * Update database paths for moved files
     */
    protected function updateDatabasePaths(): void
    {
        // Update ProjectAsset paths from project-resources/downloads to project-assets
        $oldProjectPath = config('config.uploads.project_resources_downloads');
        $newProjectPath = config('config.uploads.project-assets');

        $projectAssetUpdates = DB::table('project_assets')
            ->where('download_path', 'LIKE', $oldProjectPath.'/%')
            ->get();

        if ($projectAssetUpdates->isNotEmpty()) {
            foreach ($projectAssetUpdates as $asset) {
                $newPath = str_replace($oldProjectPath, $newProjectPath, $asset->download_path);

                DB::table('project_assets')
                    ->where('id', $asset->id)
                    ->update(['download_path' => $newPath]);
            }
        }

        // Update SiteAsset paths from resources to site-assets
        $oldSitePath = config('config.uploads.resources');
        $newSitePath = config('config.uploads.site-assets');

        $siteAssetUpdates = DB::table('site_assets')
            ->where('download_path', 'LIKE', $oldSitePath.'/%')
            ->get();

        if ($siteAssetUpdates->isNotEmpty()) {
            foreach ($siteAssetUpdates as $asset) {
                $newPath = str_replace($oldSitePath, $newSitePath, $asset->download_path);

                DB::table('site_assets')
                    ->where('id', $asset->id)
                    ->update(['download_path' => $newPath]);
            }
        }
    }

    public function processClassifications(): void
    {
        DB::table('pusher_classifications')->orderBy('id')->chunkById(100, function ($chunk) {
            $ids = [];
            foreach ($chunk as $row) {
                $data = json_decode($row->data, true);
                if ($data && is_array($data)) {
                    PusherTranscriptionJob::dispatch($data);
                    $ids[] = $row->id;
                }
            }
            if ($ids) {
                DB::table('pusher_classifications')->whereIn('id', $ids)->delete();
            }
        }, 'id');
    }
}
