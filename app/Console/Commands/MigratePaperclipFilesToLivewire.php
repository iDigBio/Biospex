<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\Resource;
use Illuminate\Console\Command;
use Intervention\Image\Laravel\Facades\Image;
use Storage;

class MigratePaperclipFilesToLivewire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:paperclip-to-livewire {--dry-run : Show what would be migrated without actually moving files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing Paperclip file attachments to new Livewire file upload structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be moved or database records updated');
        }

        $this->info('Starting Paperclip to Livewire migration...');

        // Migrate each model type
        $this->migrateProjects($isDryRun);
        $this->migrateExpeditions($isDryRun);
        $this->migrateProfiles($isDryRun);
        $this->migrateProjectResources($isDryRun);
        $this->migrateResources($isDryRun);

        $this->info('✅ Migration completed successfully!');

        if (! $isDryRun) {
            $this->info('💡 Remember to test uploads and then run cleanup migration to remove old Paperclip columns');
        }
    }

    private function migrateProjects($isDryRun)
    {
        $this->info('📁 Migrating Project logos...');

        $projects = Project::whereNotNull('logo_file_name')->get();
        $count = 0;

        foreach ($projects as $project) {
            if ($this->migrateProjectFile($project, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} project logos");
    }

    private function migrateProjectFile($project, $isDryRun)
    {
        try {
            // Use correct Paperclip path structure with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $project->id);
            $oldPath = "paperclip/App/Models/Project/logos/{$idPartition}/original/{$project->logo_file_name}";
            $newPath = "uploads/projects/logos/{$project->id}_{$project->logo_file_name}";

            if (! Storage::disk('public')->exists($oldPath)) {
                $this->warn("   ⚠️  File not found: {$oldPath}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would move: {$oldPath} → {$newPath}");

                return true;
            }

            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Copy file to new location
            $fileContent = Storage::disk('public')->get($oldPath);
            Storage::disk($disk)->put($newPath, $fileContent);

            // Update database
            $project->logo_path = $newPath;
            $project->save();

            $this->line("   ✅ Moved: {$project->title}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate {$project->title}: ".$e->getMessage());

            return false;
        }
    }

    private function migrateExpeditions($isDryRun)
    {
        $this->info('📁 Migrating Expedition logos...');

        $expeditions = Expedition::whereNotNull('logo_file_name')->get();
        $count = 0;

        foreach ($expeditions as $expedition) {
            if ($this->migrateExpeditionFile($expedition, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} expedition logos");
    }

    private function migrateExpeditionFile($expedition, $isDryRun)
    {
        try {
            // Use correct Paperclip path structure with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $expedition->id);
            $originalPath = "paperclip/App/Models/Expedition/logos/{$idPartition}/original/{$expedition->logo_file_name}";
            $mediumPath = "paperclip/App/Models/Expedition/logos/{$idPartition}/medium/{$expedition->logo_file_name}";
            $newOriginalPath = "uploads/expeditions/logos/original/{$expedition->id}_{$expedition->logo_file_name}";
            $newMediumPath = "uploads/expeditions/logos/medium/{$expedition->id}_{$expedition->logo_file_name}";

            if (! Storage::disk('public')->exists($originalPath)) {
                $this->warn("   ⚠️  File not found: {$originalPath}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would move: {$originalPath} → {$newOriginalPath}");
                if (Storage::disk('public')->exists($mediumPath)) {
                    $this->line("   Would move: {$mediumPath} → {$newMediumPath}");
                }

                return true;
            }

            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Copy original file
            $fileContent = Storage::disk('public')->get($originalPath);
            Storage::disk($disk)->put($newOriginalPath, $fileContent);

            // Copy or create medium variant
            if (Storage::disk('public')->exists($mediumPath)) {
                $mediumContent = Storage::disk('public')->get($mediumPath);
                Storage::disk($disk)->put($newMediumPath, $mediumContent);
            } else {
                // Create medium variant using Intervention Image
                $this->createExpeditionMediumVariant($fileContent, $newMediumPath, $disk);
            }

            // Update database
            $expedition->logo_path = $newOriginalPath;
            $expedition->save();

            $this->line("   ✅ Moved: {$expedition->title}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate {$expedition->title}: ".$e->getMessage());

            return false;
        }
    }

    private function createExpeditionMediumVariant($fileContent, $variantPath, $disk)
    {
        try {
            $image = Image::read($fileContent);
            $image->resize(318, 208, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $imageData = $image->encode();
            Storage::disk($disk)->put($variantPath, $imageData);
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Failed to create medium variant: '.$e->getMessage());
        }
    }

    private function migrateProfiles($isDryRun)
    {
        $this->info('📁 Migrating Profile avatars...');

        $profiles = Profile::whereNotNull('avatar_file_name')->get();
        $count = 0;

        foreach ($profiles as $profile) {
            if ($this->migrateProfileFile($profile, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} profile avatars");
    }

    private function migrateProfileFile($profile, $isDryRun)
    {
        try {
            // Use correct Paperclip path structure with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $profile->id);
            $originalPath = "paperclip/App/Models/Profile/avatars/{$idPartition}/original/{$profile->avatar_file_name}";
            $mediumPath = "paperclip/App/Models/Profile/avatars/{$idPartition}/medium/{$profile->avatar_file_name}";
            $smallPath = "paperclip/App/Models/Profile/avatars/{$idPartition}/small/{$profile->avatar_file_name}";

            $newOriginalPath = "uploads/profiles/avatars/original/{$profile->id}_{$profile->avatar_file_name}";
            $newMediumPath = "uploads/profiles/avatars/medium/{$profile->id}_{$profile->avatar_file_name}";
            $newSmallPath = "uploads/profiles/avatars/small/{$profile->id}_{$profile->avatar_file_name}";

            if (! Storage::disk('public')->exists($originalPath)) {
                $this->warn("   ⚠️  File not found: {$originalPath}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would move: {$originalPath} → {$newOriginalPath}");

                return true;
            }

            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Copy original file
            $fileContent = Storage::disk('public')->get($originalPath);
            Storage::disk($disk)->put($newOriginalPath, $fileContent);

            // Create or copy variants
            $this->createOrCopyProfileVariant($mediumPath, $newMediumPath, $fileContent, $disk, 160, 160);
            $this->createOrCopyProfileVariant($smallPath, $newSmallPath, $fileContent, $disk, 25, 25);

            // Update database
            $profile->avatar_path = $newOriginalPath;
            $profile->save();

            $this->line("   ✅ Moved: Profile ID {$profile->id}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate Profile ID {$profile->id}: ".$e->getMessage());

            return false;
        }
    }

    private function createOrCopyProfileVariant($oldPath, $newPath, $originalContent, $disk, $width, $height)
    {
        try {
            if (Storage::disk('public')->exists($oldPath)) {
                // Copy existing variant
                $variantContent = Storage::disk('public')->get($oldPath);
                Storage::disk($disk)->put($newPath, $variantContent);
            } else {
                // Create new variant
                $image = Image::read($originalContent);
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $imageData = $image->encode();
                Storage::disk($disk)->put($newPath, $imageData);
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Failed to create variant {$newPath}: ".$e->getMessage());
        }
    }

    private function migrateProjectResources($isDryRun)
    {
        $this->info('📁 Migrating Project Resource downloads...');

        $resources = ProjectResource::whereNotNull('download_file_name')->get();
        $count = 0;

        foreach ($resources as $resource) {
            if ($this->migrateProjectResourceFile($resource, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} project resource downloads");
    }

    private function migrateProjectResourceFile($resource, $isDryRun)
    {
        try {
            // Use correct Paperclip path structure with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $resource->id);
            $oldPath = "paperclip/App/Models/ProjectResource/downloads/{$idPartition}/original/{$resource->download_file_name}";

            // Note: New uploads use project UUID directories (uploads/project-resources/{project-uuid})
            // but for migration of existing files, we use the generic downloads path
            $newPath = "uploads/project-resources/downloads/{$resource->id}_{$resource->download_file_name}";

            if (! Storage::disk('public')->exists($oldPath)) {
                $this->warn("   ⚠️  File not found: {$oldPath}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would move: {$oldPath} → {$newPath}");

                return true;
            }

            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Copy file
            $fileContent = Storage::disk('public')->get($oldPath);
            Storage::disk($disk)->put($newPath, $fileContent);

            // Update database
            $resource->download_path = $newPath;
            $resource->save();

            $this->line("   ✅ Moved: {$resource->name}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate {$resource->name}: ".$e->getMessage());

            return false;
        }
    }

    private function migrateResources($isDryRun)
    {
        $this->info('📁 Migrating Resource documents...');

        $resources = Resource::whereNotNull('document_file_name')->get();
        $count = 0;

        foreach ($resources as $resource) {
            if ($this->migrateResourceFile($resource, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} resource documents");
    }

    private function migrateResourceFile($resource, $isDryRun)
    {
        try {
            // Use correct Paperclip path structure with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $resource->id);
            $oldPath = "paperclip/App/Models/Resource/documents/{$idPartition}/original/{$resource->document_file_name}";
            $newPath = "uploads/resources/{$resource->id}_{$resource->document_file_name}";

            if (! Storage::disk('public')->exists($oldPath)) {
                $this->warn("   ⚠️  File not found: {$oldPath}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would move: {$oldPath} → {$newPath}");

                return true;
            }

            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Copy file
            $fileContent = Storage::disk('public')->get($oldPath);
            Storage::disk($disk)->put($newPath, $fileContent);

            // Update database
            $resource->download_path = $newPath;
            $resource->save();

            $this->line("   ✅ Moved: {$resource->title}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate {$resource->title}: ".$e->getMessage());

            return false;
        }
    }
}
