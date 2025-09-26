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

namespace App\Services\Process;

use App\Models\Subject;
use App\Services\Csv\Csv;
use App\Services\Project\HeaderService;
use Exception;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;

/**
 * Darwin Core Batch Processor
 *
 * Memory-efficient batch processing engine for Darwin Core imports.
 * Handles single-pass processing with complete occurrence data embedding.
 */
class DwcBatchProcessor
{
    private const BATCH_SIZE = 1000;

    private array $duplicates = [];

    private array $rejectedMedia = [];

    private int $subjectCount = 0;

    private array $properties = [];

    private int $projectId;

    public function __construct(
        private MetaFile $metaFile,
        private Csv $csv,
        private DwcValidationService $validation,
        private HeaderService $headerService
    ) {
        // Configuration values are now injected in DwcValidationService and MetaFile constructors
    }

    /**
     * Process Darwin Core Archive with batch processing.
     */
    public function processArchive(int $projectId, string $directory): array
    {
        $this->projectId = $projectId;

        Log::info('Starting Darwin Core batch processing', ['project_id' => $projectId, 'directory' => $directory]);

        try {
            // Parse meta.xml
            $metaFile = $directory.'/meta.xml';
            $this->metaFile->process($metaFile);

            // Save meta file to database
            $this->metaFile->saveMetaFile($projectId, file_get_contents($metaFile));

            $mediaIsCore = $this->metaFile->getMediaIsCore();
            $metaFields = $this->metaFile->getMetaFields();

            // Validate identifier columns exist
            if (! $this->validation->checkForIdentifierColumn($metaFields['extension'])) {
                throw new Exception('No identifier columns found in meta.xml extension fields');
            }

            // Load occurrence data into memory map
            $occurrenceData = $this->loadOccurrenceData($directory, $mediaIsCore, $metaFields);

            // Process media file with validation and batch operations
            $this->processMediaWithValidation($directory, $mediaIsCore, $metaFields, $occurrenceData, $projectId);

            Log::info('Darwin Core batch processing completed', [
                'project_id' => $projectId,
                'subjects_created' => $this->subjectCount,
                'duplicates' => count($this->duplicates),
                'rejected' => count($this->rejectedMedia),
            ]);

            return [
                'success' => true,
                'subjects_created' => $this->subjectCount,
                'duplicates_count' => count($this->duplicates),
                'rejected_count' => count($this->rejectedMedia),
            ];

        } catch (Exception $e) {
            Log::error('Darwin Core batch processing failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Load occurrence data into memory map for fast lookup.
     */
    protected function loadOccurrenceData(string $directory, bool $mediaIsCore, array $metaFields): array
    {
        $occurrenceData = [];

        // If media is core, no separate occurrence file exists
        if ($mediaIsCore) {
            return $occurrenceData;
        }

        $occurrenceFile = $directory.'/'.$this->metaFile->getCoreFile();

        if (! file_exists($occurrenceFile)) {
            Log::warning('Occurrence file not found', ['file' => $occurrenceFile]);

            return $occurrenceData;
        }

        Log::info('Loading occurrence data', ['file' => $occurrenceFile]);

        $this->csv->readerCreateFromPath($occurrenceFile);
        $this->csv->setDelimiter($this->metaFile->getCoreDelimiter());
        $this->csv->setEnclosure($this->metaFile->getCoreEnclosure());
        $this->csv->setHeaderOffset(0);

        $header = $this->csv->getHeader();
        $records = $this->csv->getRecords();

        foreach ($records as $row) {
            if (empty($row) || count($row) !== count($header)) {
                continue;
            }

            // Use first column (ID) as key for lookup
            $occurrenceId = $row[$header[0]] ?? null;
            if ($occurrenceId) {
                $occurrenceRecord = array_combine($header, $row);
                // Sanitize occurrence data for UTF-8 issues
                $occurrenceData[$occurrenceId] = $this->validation->sanitizeOccurrenceData([$occurrenceId => $occurrenceRecord])[$occurrenceId];
            }
        }

        Log::info('Loaded occurrence records', ['count' => count($occurrenceData)]);

        return $occurrenceData;
    }

    /**
     * Process media file with validation and batch operations.
     */
    protected function processMediaWithValidation(
        string $directory,
        bool $mediaIsCore,
        array $metaFields,
        array $occurrenceData,
        int $projectId
    ): void {
        $mediaFile = $directory.'/'.$this->metaFile->getExtensionFile();

        if (! file_exists($mediaFile)) {
            throw new Exception("Media file not found: {$mediaFile}");
        }

        Log::info('Processing media file', ['file' => $mediaFile]);

        $this->csv->readerCreateFromPath($mediaFile);
        $this->csv->setDelimiter($this->metaFile->getExtDelimiter());
        $this->csv->setEnclosure($this->metaFile->getExtEnclosure());
        $this->csv->setHeaderOffset(0);

        $header = $this->csv->getHeader();
        $records = $this->csv->getRecords();

        // Save header for property creation
        $this->saveHeaderArray($header, $mediaIsCore);

        $batch = [];
        $rowCount = 0;

        foreach ($records as $row) {
            if (empty($row) || count($row) !== count($header)) {
                continue;
            }

            $rowCount++;
            $rowData = array_combine($header, $row);
            $batch[] = $rowData;

            // Process batch when it reaches the batch size
            if (count($batch) >= self::BATCH_SIZE) {
                $this->processBatch($batch, $projectId, $metaFields['extension'], $header, $occurrenceData, $mediaIsCore);
                $batch = [];

                // Memory cleanup
                if ($rowCount % (self::BATCH_SIZE * 5) === 0) {
                    gc_collect_cycles();
                }
            }
        }

        // Process remaining items in final batch
        if (! empty($batch)) {
            $this->processBatch($batch, $projectId, $metaFields['extension'], $header, $occurrenceData, $mediaIsCore);
        }

        Log::info('Media file processing completed', ['rows_processed' => $rowCount]);
    }

    /**
     * Process a batch of media rows.
     */
    protected function processBatch(
        array $batch,
        int $projectId,
        array $metaFields,
        array $header,
        array $occurrenceData,
        bool $mediaIsCore
    ): void {
        // Validate the entire batch
        $validationResult = $this->validation->validateBatch($batch, $header, $metaFields, $projectId);

        // Add rejected records to collection
        $this->rejectedMedia = array_merge($this->rejectedMedia, $validationResult['rejected']);

        if (empty($validationResult['valid'])) {
            return;
        }

        // Build subjects for valid records
        $subjects = [];
        foreach ($validationResult['valid'] as $row) {
            $subject = $this->buildSubject($row, $header, $projectId, $occurrenceData, $mediaIsCore);
            if ($subject) {
                $subjects[] = $subject;
            }
        }

        if (! empty($subjects)) {
            // Bulk insert subjects
            $this->bulkInsertSubjects($subjects);
            $this->subjectCount += count($subjects);

            Log::debug('Processed batch', [
                'valid_records' => count($validationResult['valid']),
                'subjects_created' => count($subjects),
                'rejected_records' => count($validationResult['rejected']),
            ]);
        }
    }

    /**
     * Build a complete subject with embedded occurrence data.
     */
    protected function buildSubject(
        array $row,
        array $header,
        int $projectId,
        array $occurrenceData,
        bool $mediaIsCore
    ): ?array {
        try {
            // Get occurrence ID for lookup (first column in media file points to occurrence ID)
            $occurrenceId = $mediaIsCore ? null : $row[$header[0]] ?? null;

            // Base subject fields
            $fields = [
                'project_id' => $projectId,
                'ocr' => '',
                'expedition_ids' => [],
                'exported' => false,
            ];

            // Embed occurrence data if available
            $occurrence = [];
            if ($occurrenceId && isset($occurrenceData[$occurrenceId])) {
                $occurrence = ['occurrence' => $occurrenceData[$occurrenceId]];
            } elseif (! $mediaIsCore) {
                // Add occurrence stub with ID for non-media-core imports
                $occurrence = ['occurrence' => ['id' => (string) $occurrenceId]];
            }

            // Combine all data
            $subject = $fields + $row + $occurrence;

            // Set MongoDB ObjectId
            $subject['_id'] = new ObjectId;

            return $subject;

        } catch (Exception $e) {
            Log::error('Error building subject', [
                'error' => $e->getMessage(),
                'row' => $row,
            ]);

            $rejected = ['Reason' => 'Error building subject: '.$e->getMessage()] + $row;
            $this->rejectedMedia[] = $rejected;

            return null;
        }
    }

    /**
     * Bulk insert subjects using MongoDB operations.
     */
    private function bulkInsertSubjects(array $subjects): void
    {
        try {
            // Use MongoDB bulk operations for efficiency
            Subject::raw(function ($collection) use ($subjects) {
                return $collection->insertMany($subjects);
            });

        } catch (Exception $e) {
            Log::error('Bulk insert failed', [
                'error' => $e->getMessage(),
                'subject_count' => count($subjects),
            ]);

            // Fallback to individual inserts
            foreach ($subjects as $subject) {
                try {
                    Subject::create($subject);
                } catch (Exception $individualError) {
                    Log::error('Individual subject insert failed', [
                        'error' => $individualError->getMessage(),
                        'subject_id' => $subject['imageId'] ?? 'unknown',
                    ]);

                    $rejected = ['Reason' => 'Database insert failed: '.$individualError->getMessage()];
                    $this->rejectedMedia[] = $rejected;
                }
            }
        }
    }

    /**
     * Save header array for property creation.
     */
    private function saveHeaderArray(array $header, bool $loadMedia): void
    {
        try {
            $type = $loadMedia ? 'image' : 'occurrence';

            $result = $this->headerService->getFirst('project_id', $this->projectId);

            if (empty($result)) {
                $insert = [
                    'project_id' => $this->projectId,
                    'header' => [$type => $header],
                ];
                $this->headerService->create($insert);
            } else {
                $existingHeader = $result->header;
                $existingHeader[$type] = isset($existingHeader[$type]) ? $this->combineHeader($existingHeader[$type], $header) : array_unique($header);
                $result->header = $existingHeader;
                $result->save();
            }
        } catch (Exception $e) {
            Log::error('Failed to save header array', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Combine saved header with new header.
     */
    private function combineHeader(array $resHeader, array $newHeader): array
    {
        return array_unique(array_merge($resHeader, array_diff($newHeader, $resHeader)));
    }

    /**
     * Get duplicates for reporting.
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * Get rejected media for reporting.
     */
    public function getRejectedMedia(): array
    {
        return $this->rejectedMedia;
    }

    /**
     * Get subject count.
     */
    public function getSubjectCount(): int
    {
        return $this->subjectCount;
    }
}
