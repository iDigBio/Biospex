<?php

namespace App\Console\Commands;

use App\Models\Header;
use App\Models\Subject;
use App\Services\Project\HeaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fix Darwin Core Import Header Issues
 *
 * This command fixes header records that are missing the 'image' section
 * by building headers from actual MongoDB subjects data.
 */
class FixDarwinCoreHeadersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'biospex:fix-dwc-headers 
                            {project-id : The project ID to fix headers for}
                            {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Fix Darwin Core import headers by rebuilding from actual MongoDB subjects data';

    /**
     * Execute the console command.
     */
    public function handle(HeaderService $headerService): int
    {
        $this->info('Starting Darwin Core header fix process...');

        $dryRun = $this->option('dry-run');
        $verbose = $this->option('verbose');
        $projectId = $this->argument('project-id');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        try {
            // Get the specific header for this project
            $header = Header::where('project_id', $projectId)->first();

            if (! $header) {
                $this->error("No header found for project ID: {$projectId}");

                return self::FAILURE;
            }

            $this->info("Processing project ID: {$projectId}");

            $headerData = $header->header;
            $isProblematic = $this->isHeaderProblematic($headerData);

            if (! $isProblematic) {
                $this->info("Header for project {$projectId} is already complete: ".$this->getHeaderDescription($headerData));

                return self::SUCCESS;
            }

            if ($verbose) {
                $this->line('Current header: '.$this->getHeaderDescription($headerData));
            }

            if (! $dryRun) {
                $fixed = $this->fixHeaderFromAllSubjects($header, $headerData, $verbose);
                if ($fixed) {
                    $this->info('Darwin Core header fix completed successfully!');
                } else {
                    $this->warn('Header could not be fixed - check logs for details');
                }
            } else {
                $this->warn('DRY RUN - Run without --dry-run to apply fixes');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error fixing Darwin Core headers: '.$e->getMessage());
            Log::error('Darwin Core header fix failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Check if a header is problematic (missing image section or has only occurrence).
     */
    private function isHeaderProblematic(array $headerData): bool
    {
        // Case 1: Only has 'occurrence', missing 'image'
        if (isset($headerData['occurrence']) && ! isset($headerData['image'])) {
            return true;
        }

        // Case 2: Has 'image' but it's empty while 'occurrence' has data
        if (isset($headerData['occurrence']) && isset($headerData['image']) &&
            empty($headerData['image']) && ! empty($headerData['occurrence'])) {
            return true;
        }

        return false;
    }

    /**
     * Get a description of the header structure for verbose output.
     */
    private function getHeaderDescription(array $headerData): string
    {
        $parts = [];

        if (isset($headerData['occurrence'])) {
            $count = is_array($headerData['occurrence']) ? count($headerData['occurrence']) : 0;
            $parts[] = "occurrence({$count})";
        }

        if (isset($headerData['image'])) {
            $count = is_array($headerData['image']) ? count($headerData['image']) : 0;
            $parts[] = "image({$count})";
        }

        return implode(', ', $parts) ?: 'empty';
    }

    /**
     * Fix header by processing all subjects for the project in batches.
     */
    private function fixHeaderFromAllSubjects(Header $header, array $headerData, bool $verbose): bool
    {
        try {
            $projectId = $header->project_id;
            $batchSize = 1000; // Process 1000 subjects at a time

            // Get total count first
            $totalSubjects = Subject::where('project_id', $projectId)->count();

            if ($totalSubjects === 0) {
                $this->warn("No subjects found for project {$projectId}");

                return false;
            }

            $this->info("Processing {$totalSubjects} subjects in batches of {$batchSize}");

            // Use sets to avoid duplicates while building comprehensive headers
            $imageFieldsSet = [];
            $occurrenceFieldsSet = [];
            $processedCount = 0;

            // Process subjects in batches using cursor
            Subject::where('project_id', $projectId)
                ->orderBy('_id')
                ->chunk($batchSize, function ($subjects) use (&$imageFieldsSet, &$occurrenceFieldsSet, &$processedCount, $verbose, $totalSubjects) {
                    foreach ($subjects as $subject) {
                        // Get raw attributes to avoid lazy loading
                        $subjectArray = $subject->getAttributes();

                        // Process image fields (root level, excluding system fields)
                        $this->extractImageFields($subjectArray, $imageFieldsSet);

                        // Process occurrence fields (from embedded document)
                        $this->extractOccurrenceFields($subjectArray, $occurrenceFieldsSet);

                        $processedCount++;
                    }

                    if ($verbose) {
                        $this->line("  Processed {$processedCount}/{$totalSubjects} subjects - Image fields: ".count($imageFieldsSet).', Occurrence fields: '.count($occurrenceFieldsSet));
                    }
                });

            // Convert sets to indexed arrays
            $imageHeaders = array_values($imageFieldsSet);
            $occurrenceHeaders = array_values($occurrenceFieldsSet);

            if (empty($imageHeaders)) {
                $this->warn('Could not extract any image headers from subjects');

                return false;
            }

            DB::beginTransaction();

            // Update the header structure
            $newHeaderData = $headerData;
            $newHeaderData['image'] = $imageHeaders;
            $newHeaderData['occurrence'] = $occurrenceHeaders;

            $header->header = $newHeaderData;
            $header->save();

            DB::commit();

            $this->info('Header updated successfully:');
            $this->line('  - Image fields: '.count($imageHeaders));
            $this->line('  - Occurrence fields: '.count($occurrenceHeaders));
            $this->line("  - Processed subjects: {$processedCount}");

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to fix header for project', [
                'project_id' => $header->project_id,
                'error' => $e->getMessage(),
            ]);

            $this->error('Fix failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Extract image fields from subject document.
     */
    private function extractImageFields(array $subjectData, array &$imageFieldsSet): void
    {
        // Fields that should NOT be in image headers
        $excludeFields = [
            'project_id', 'ocr', 'expedition_ids', 'exported', '_id', 'occurrence', 'updated_at', 'created_at',
        ];

        foreach ($subjectData as $key => $value) {
            if (! in_array($key, $excludeFields)) {
                $imageFieldsSet[$key] = $key; // Use key as both key and value to create a set
            }
        }
    }

    /**
     * Extract occurrence fields from embedded occurrence document.
     */
    private function extractOccurrenceFields(array $subjectData, array &$occurrenceFieldsSet): void
    {
        if (isset($subjectData['occurrence']) && is_array($subjectData['occurrence'])) {
            foreach ($subjectData['occurrence'] as $key => $value) {
                $occurrenceFieldsSet[$key] = $key; // Use key as both key and value to create a set
            }
        }
    }
}
