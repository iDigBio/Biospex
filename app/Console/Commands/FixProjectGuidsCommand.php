<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class FixProjectGuidsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-project-guid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix ProjectGUID values in advertise data to match project UUID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all projects with advertise data
        $projects = Project::whereNotNull('advertise')
            ->where('advertise', '!=', '')
            ->get();

        $projectsNeedingUpdate = collect();

        // Check each project to see if it needs updating
        foreach ($projects as $project) {
            $advertiseData = $this->decodeAdvertiseData($project->advertise);

            if ($advertiseData && isset($advertiseData['ProjectGUID']) && $advertiseData['ProjectGUID'] !== $project->uuid) {
                $projectsNeedingUpdate->push($project);
            }
        }

        $this->info("Found {$projectsNeedingUpdate->count()} projects that need ProjectGUID updates");

        if ($projectsNeedingUpdate->isEmpty()) {
            $this->info('No projects need ProjectGUID updates');

            return 0;
        }

        // Display what will be changed
        foreach ($projectsNeedingUpdate as $project) {
            $advertiseData = $this->decodeAdvertiseData($project->advertise);
            $this->line("Project ID {$project->id}: '{$advertiseData['ProjectGUID']}' -> '{$project->uuid}'");
        }

        if ($this->confirm('Do you want to proceed with updating these ProjectGUIDs?')) {
            $updatedCount = 0;

            foreach ($projectsNeedingUpdate as $project) {
                try {
                    $advertiseData = $this->decodeAdvertiseData($project->advertise);

                    if ($advertiseData) {
                        $advertiseData['ProjectGUID'] = $project->uuid;

                        // Clean UTF-8 encoding and save as JSON
                        $cleanedData = $this->cleanUtf8InArray($advertiseData);
                        $project->advertise = json_encode($cleanedData, JSON_UNESCAPED_UNICODE);
                        $project->save();

                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error updating Project ID {$project->id}: ".$e->getMessage());
                }
            }

            $this->info("Updated {$updatedCount} ProjectGUIDs successfully");

            // Clear cache after updates
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $this->info('Cache cleared');
        }

        return 0;
    }

    /**
     * Decode advertise data - the Project model already unserializes the data
     */
    private function decodeAdvertiseData($advertiseValue)
    {
        if (empty($advertiseValue)) {
            return null;
        }

        // If it's already an array (unserialized by the model), return it
        if (is_array($advertiseValue)) {
            return $advertiseValue;
        }

        // If it's a string, try to decode as JSON first
        if (is_string($advertiseValue)) {
            $jsonDecoded = json_decode($advertiseValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonDecoded;
            }

            // If JSON decode fails, try unserialize (for legacy data)
            try {
                $unserialized = unserialize($advertiseValue);
                if ($unserialized !== false) {
                    return $unserialized;
                }
            } catch (\Exception $e) {
                // Ignore unserialize errors
            }
        }

        return null;
    }

    /**
     * Recursively clean UTF-8 encoding in array values
     */
    private function cleanUtf8InArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8InArray($value);
            }
        } elseif (is_string($data)) {
            // Clean UTF-8 encoding using the GeneralService method
            $generalService = app(\App\Services\Helpers\GeneralService::class);
            $data = $generalService->forceUtf8($data, 'UTF-8');
        }

        return $data;
    }
}
