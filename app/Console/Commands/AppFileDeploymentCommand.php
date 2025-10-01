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

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

/**
 * Class AppFileDeploymentCommand
 */
class AppFileDeploymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy-files {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles moving, renaming, and replacing files needed per environment settings';

    private Collection $replacements;

    private bool $isDryRun = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->isDryRun = $this->option('dry-run');
            $this->info('Starting file deployment process...');

            if ($this->isDryRun) {
                $this->warn('DRY RUN MODE: No files will be modified');
            }

            $this->buildReplacementMap();
            $sourceFiles = $this->getSourceFiles();
            $processedFiles = $this->processFiles($sourceFiles);

            $this->info("Successfully processed {$processedFiles} file(s)");

            return CommandAlias::SUCCESS;

        } catch (Exception $e) {
            $this->error("Deployment failed: {$e->getMessage()}");

            return CommandAlias::FAILURE;
        }
    }

    /**
     * Get source files from supervisor directory.
     */
    private function getSourceFiles(): Collection
    {
        $supervisorPath = base_path('resources/supervisor');

        if (! File::isDirectory($supervisorPath)) {
            throw new Exception("Supervisor templates directory not found: {$supervisorPath}");
        }

        $files = File::files($supervisorPath);

        if (empty($files)) {
            throw new Exception("No supervisor template files found in: {$supervisorPath}");
        }

        $this->info('Found '.count($files).' supervisor template file(s)');

        return collect($files);
    }

    /**
     * Process all template files.
     */
    private function processFiles(Collection $sourceFiles): int
    {
        $this->ensureTargetDirectory();
        $processedCount = 0;

        $progressBar = $this->output->createProgressBar($sourceFiles->count());
        $progressBar->setFormat('Processing: %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($sourceFiles as $sourceFile) {
            $progressBar->setMessage($sourceFile->getBasename());

            try {
                $this->processFile($sourceFile);
                $processedCount++;
            } catch (Exception $e) {
                $progressBar->clear();
                $this->error("Failed to process {$sourceFile->getBasename()}: {$e->getMessage()}");
                $progressBar->display();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        return $processedCount;
    }

    /**
     * Process a single template file.
     */
    private function processFile($sourceFile): void
    {
        $targetPath = $this->getTargetPath($sourceFile);

        // Read source content
        $content = File::get($sourceFile->getPathname());

        if (empty($content)) {
            throw new Exception('Source file is empty or unreadable');
        }

        // Apply all replacements
        $processedContent = $this->applyReplacements($content);

        // Write to target (unless dry run)
        if (! $this->isDryRun) {
            $this->writeTargetFile($targetPath, $processedContent);
        }

        // Show changes in dry run mode
        if ($this->isDryRun) {
            $this->showDryRunChanges($sourceFile->getBasename(), $content, $processedContent);
        }
    }

    /**
     * Apply all configured replacements to content.
     */
    private function applyReplacements(string $content): string
    {
        $processed = $content;

        foreach ($this->replacements as $search => $replace) {
            if ($replace !== null) {
                // Allow empty string replacements for REVERB_DEBUG
                if ($search === 'REVERB_DEBUG' || $replace !== '') {
                    $processed = str_replace($search, $replace, $processed);
                }
            }
        }

        return $processed;
    }

    /**
     * Ensure target directory exists.
     */
    private function ensureTargetDirectory(): void
    {
        if (! $this->isDryRun && ! Storage::exists('supervisor')) {
            Storage::makeDirectory('supervisor');
            $this->info('Created supervisor directory');
        }
    }

    /**
     * Get target file path.
     */
    private function getTargetPath($sourceFile): string
    {
        return Storage::path('supervisor').DIRECTORY_SEPARATOR.$sourceFile->getBasename();
    }

    /**
     * Write content to target file.
     */
    private function writeTargetFile(string $targetPath, string $content): void
    {
        // Remove existing file if it exists
        if (File::exists($targetPath)) {
            File::delete($targetPath);
        }

        // Write new content
        if (! File::put($targetPath, $content)) {
            throw new Exception("Failed to write target file: {$targetPath}");
        }
    }

    /**
     * Show what changes would be made in dry run mode.
     */
    private function showDryRunChanges(string $filename, string $original, string $processed): void
    {
        if ($original !== $processed) {
            $this->line("\n<fg=yellow>Changes for {$filename}:</>");

            $changes = 0;
            foreach ($this->replacements as $search => $replace) {
                if ($replace !== null && $replace !== '' && str_contains($original, $search)) {
                    $this->line("  <fg=cyan>{$search}</> → <fg=green>{$replace}</>");
                    $changes++;
                }
            }

            if ($changes === 0) {
                $this->line('  <fg=gray>No replacements needed</>');
            }
        }
    }

    /**
     * Build the replacement map from configuration.
     */
    private function buildReplacementMap(): void
    {
        $deploymentFields = config('config.deployment_fields');

        if (empty($deploymentFields)) {
            throw new Exception('No deployment fields configured');
        }

        $this->replacements = collect($deploymentFields)
            ->mapWithKeys(function ($field) {
                $replacement = $this->getReplacementValue($field);

                return [$field => $replacement];
            })
            ->filter(function ($value, $key) {
                // Allow empty strings for REVERB_DEBUG (when false/empty, it should be replaced with '')
                if ($key === 'REVERB_DEBUG') {
                    return $value !== null;
                }

                return $value !== null && $value !== '';
            });

        $this->info('Built replacement map with '.$this->replacements->count().' entries');
    }

    /**
     * Get replacement value for a configuration field.
     */
    private function getReplacementValue(string $field): ?string
    {
        try {
            if (str_starts_with($field, 'APP_')) {
                if ($field === 'APP_ENV') {
                    return config('app.env');
                }

                return config('config.'.strtolower($field));
            }

            if ($field === 'REVERB_DEBUG') {
                return config('config.reverb_debug') ? '--debug' : '';
            }

            if ($field === 'SUPERVISOR_GROUP') {
                return config('config.supervisor_group');
            }

            return config('config.'.strtolower(Str::replaceFirst('_', '.', $field)));

        } catch (Exception $e) {
            $this->warn("Could not resolve configuration for {$field}: {$e->getMessage()}");

            return null;
        }
    }
}
