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

namespace App\Jobs;

use App\Models\Import;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Services\Process\CreateReportService;
use App\Services\Process\DwcBatchProcessor;
use App\Services\Project\ProjectService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Notification;
use Throwable;

/**
 * Darwin Core Batch Import Job
 *
 * Enhanced queue job with batch processing, improved error handling,
 * and better user notifications with import statistics.
 */
class DwcBatchImportJob implements ShouldQueue
{
    use ButtonTrait, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 7200; // Increased timeout for large files

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(protected Import $import)
    {
        $this->import = $import->withoutRelations();
        $this->onQueue(config('config.queue.import'));
    }

    public function handle(
        ProjectService $projectService,
        DwcBatchProcessor $batchProcessor,
        CreateReportService $createReportService
    ): void {
        $startTime = microtime(true);
        $scratchFileDir = Storage::disk('efs')->path(config('config.scratch_dir').'/'.md5($this->import->file));
        $importFilePath = Storage::disk('efs')->path($this->import->file);

        $project = $projectService->getProjectForDarwinImportJob($this->import->project_id);
        $users = $project->group->users->push($project->group->owner);

        Log::info('Starting Darwin Core batch import', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'file' => $this->import->file,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Create scratch directory with proper permissions
            $this->makeDirectory($scratchFileDir);

            // Extract archive with enhanced error checking
            $this->unzipArchive($importFilePath, $scratchFileDir);

            // Process with new batch processor
            $result = $batchProcessor->processArchive($this->import->project_id, $scratchFileDir);

            // Generate comprehensive reports
            $reports = $this->generateReports($createReportService, $batchProcessor);

            // Calculate processing time
            $processingTime = (float) number_format(microtime(true) - $startTime, 2, '.', '');

            // Send success notification with detailed statistics
            $this->sendSuccessNotification($users, $project, $result, $reports, $processingTime);

            // Trigger OCR processing
            TesseractOcrCreateJob::dispatch($project);

            // Cleanup files
            $this->cleanupFiles($scratchFileDir, $importFilePath);

            Log::info('Darwin Core batch import completed successfully', [
                'import_id' => $this->import->id,
                'subjects_created' => $result['subjects_created'],
                'processing_time' => $processingTime.'s',
            ]);

            $this->import->delete();
            $this->delete();

        } catch (Throwable $throwable) {
            $this->handleImportFailure($throwable, $users, $project, $scratchFileDir, $startTime);
        }
    }

    /**
     * Handle job failure with enhanced error reporting.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Darwin Core batch import job failed permanently', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Mark import as failed
        $this->import->error = 1;
        $this->import->save();
    }

    /**
     * Create directory with proper error handling.
     */
    private function makeDirectory(string $dir): void
    {
        if (File::exists($dir)) {
            File::cleanDirectory($dir);
        } elseif (! File::makeDirectory($dir, 0775, true)) {
            throw new Exception(t('Unable to create directory: :directory', [':directory' => $dir]));
        }

        if (! File::isWritable($dir) && ! chmod($dir, 0775)) {
            throw new Exception(t('Unable to make directory writable: %s', $dir));
        }
    }

    /**
     * Enhanced archive extraction with error checking.
     */
    private function unzipArchive(string $zipFile, string $dir): void
    {
        if (! file_exists($zipFile)) {
            throw new Exception("Archive file not found: {$zipFile}");
        }

        // Use shell_exec with error checking
        $command = "unzip -q \"$zipFile\" -d \"$dir\" 2>&1";
        $output = shell_exec($command);
        $exitCode = shell_exec('echo $?');

        if ($exitCode != 0) {
            throw new Exception("Failed to extract archive. Output: {$output}");
        }

        // Verify meta.xml exists
        if (! file_exists($dir.'/meta.xml')) {
            throw new Exception('Invalid Darwin Core Archive: meta.xml not found');
        }

        Log::info('Archive extracted successfully', ['directory' => $dir]);
    }

    /**
     * Generate comprehensive reports.
     */
    private function generateReports(CreateReportService $createReportService, DwcBatchProcessor $batchProcessor): array
    {
        $reports = [];

        // Generate duplicates report
        $duplicates = $batchProcessor->getDuplicates();
        if (! empty($duplicates)) {
            $dupsCsvName = md5($this->import->id).'_duplicates.csv';
            $dupName = $createReportService->createCsvReport($dupsCsvName, $duplicates);

            if ($dupName) {
                $reports['duplicates'] = [
                    'name' => $dupName,
                    'route' => route('admin.downloads.report', ['file' => $dupName]),
                    'count' => count($duplicates),
                ];
            }
        }

        // Generate rejected records report
        $rejected = $batchProcessor->getRejectedMedia();
        if (! empty($rejected)) {
            $rejCsvName = md5($this->import->id).'_rejected.csv';
            $rejName = $createReportService->createCsvReport($rejCsvName, $rejected);

            if ($rejName) {
                $reports['rejected'] = [
                    'name' => $rejName,
                    'route' => route('admin.downloads.report', ['file' => $rejName]),
                    'count' => count($rejected),
                ];
            }
        }

        return $reports;
    }

    /**
     * Send enhanced success notification.
     */
    private function sendSuccessNotification(
        $users,
        $project,
        array $result,
        array $reports,
        $processingTime
    ): void {
        $buttons = [];

        // Add report download buttons
        if (isset($reports['duplicates'])) {
            $buttons = array_merge($buttons, $this->createButton(
                $reports['duplicates']['route'],
                t('View Duplicate Records (%d)', $reports['duplicates']['count'])
            ));
        }

        if (isset($reports['rejected'])) {
            $buttons = array_merge($buttons, $this->createButton(
                $reports['rejected']['route'],
                t('View Rejected Records (%d)', $reports['rejected']['count']),
                'error'
            ));
        }

        $htmlMessages = [
            t('The Darwin Core import for %s has been completed successfully.', $project->title),
            '',
            '<strong>'.t('Import Statistics:').'</strong>',
            t('• Subjects created: %d', $result['subjects_created']),
            t('• Processing time: %s seconds', $processingTime),
        ];

        if (isset($reports['duplicates'])) {
            $htmlMessages[] = t('• Duplicate records: %d', $reports['duplicates']['count']);
        }

        if (isset($reports['rejected'])) {
            $htmlMessages[] = t('• Rejected records: %d', $reports['rejected']['count']);
        }

        $htmlMessages[] = '';
        $htmlMessages[] = t('OCR processing may take longer and you will receive an email when it is complete.');

        $attributes = [
            'subject' => t('Darwin Core Import Complete - %s', $project->title),
            'html' => $htmlMessages,
            'buttons' => $buttons,
        ];

        Notification::send($users, new Generic($attributes));
    }

    /**
     * Handle import failure with comprehensive error reporting.
     */
    private function handleImportFailure(
        Throwable $throwable,
        $users,
        $project,
        string $scratchFileDir,
        float $startTime
    ): void {
        $processingTime = (float) number_format(microtime(true) - $startTime, 2, '.', '');

        Log::error('Darwin Core batch import failed', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'attempt' => $this->attempts(),
            'max_attempts' => $this->tries,
            'processing_time' => $processingTime.'s',
            'error' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]);

        // Mark import as failed if this is the last attempt
        if ($this->attempts() >= $this->tries) {
            $this->import->error = 1;
            $this->import->save();
        }

        // Cleanup scratch directory
        $this->cleanupScratchDirectory($scratchFileDir);

        // Send failure notification only on final failure
        if ($this->attempts() >= $this->tries) {
            $this->sendFailureNotification($users, $project, $throwable, $processingTime);
        }

        // Only delete job on final failure
        if ($this->attempts() >= $this->tries) {
            $this->delete();
        } else {
            // Release job for retry
            $this->release($this->backoff);
        }
    }

    /**
     * Send comprehensive failure notification.
     */
    private function sendFailureNotification(
        $users,
        $project,
        Throwable $throwable,
        $processingTime
    ): void {
        $attributes = [
            'subject' => t('Darwin Core Import Failed - %s', $project->title),
            'html' => [
                t('An error occurred while importing the Darwin Core Archive.'),
                '',
                '<strong>'.t('Error Details:').'</strong>',
                t('• Project: %s', $project->title),
                t('• Project ID: %s', $project->id),
                t('• Processing time: %s seconds', $processingTime),
                t('• Error: %s', $throwable->getMessage()),
                t('• File: %s', basename($throwable->getFile())),
                t('• Line: %s', $throwable->getLine()),
                '',
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];

        Notification::send($users, new Generic($attributes, true));
    }

    /**
     * Clean up files after successful import.
     */
    private function cleanupFiles(string $scratchFileDir, string $importFilePath): void
    {
        $this->cleanupScratchDirectory($scratchFileDir);

        if (file_exists($importFilePath)) {
            File::delete($importFilePath);
        }
    }

    /**
     * Clean up scratch directory.
     */
    private function cleanupScratchDirectory(string $scratchFileDir): void
    {
        if (File::isDirectory($scratchFileDir)) {
            File::cleanDirectory($scratchFileDir);
            File::deleteDirectory($scratchFileDir);
        }
    }
}
