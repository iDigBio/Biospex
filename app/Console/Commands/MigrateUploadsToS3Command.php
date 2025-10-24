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

use App\Models\Expedition;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectAsset;
use App\Models\SiteAsset;
use Illuminate\Console\Command;
use Intervention\Image\Laravel\Facades\Image;
use Storage;

class MigrateUploadsToS3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:paperclip-uploads-to-s3 {--dry-run : Show what would be migrated without actually moving files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing Paperclip files from storage/app/public/paperclip to S3 using config.uploads paths';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be moved');
        }

        $this->info('Starting migration of Paperclip files from storage/app/public/paperclip to S3...');

        // Migrate each Paperclip file type to S3
        $this->migrateProjectLogos($isDryRun);
        $this->migrateExpeditionLogos($isDryRun);
        $this->migrateProfileAvatars($isDryRun);
        $this->migrateProjectAssetDownloads($isDryRun);
        $this->migrateSiteAssetDocuments($isDryRun);

        $this->info('✅ Migration completed successfully!');

        if (! $isDryRun) {
            $this->info('💡 Paperclip files have been migrated to S3. Local paperclip files remain for backup.');
        }
    }

    private function migrateProjectLogos($isDryRun)
    {
        $this->info('📁 Migrating Project logos from paperclip to S3...');

        $projects = Project::whereNotNull('logo_file_name')->get();
        $count = 0;

        foreach ($projects as $project) {
            if ($this->migratePaperclipFileToS3($project, 'Project', 'logos', $project->logo_file_name, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} project logos");
    }

    private function migrateExpeditionLogos($isDryRun)
    {
        $this->info('📁 Migrating Expedition logos from paperclip to S3...');

        $expeditions = Expedition::whereNotNull('logo_file_name')->get();
        $count = 0;

        foreach ($expeditions as $expedition) {
            if ($this->migratePaperclipFileToS3($expedition, 'Expedition', 'logos', $expedition->logo_file_name, $isDryRun)) {

                // Also migrate or create medium variant
                $this->migrateOrCreateExpeditionMedium($expedition, $isDryRun);
                $count++;
            }
        }

        $this->info("   Processed {$count} expedition logos");
    }

    private function migrateOrCreateExpeditionMedium($expedition, $isDryRun)
    {
        try {
            $filename = "{$expedition->id}_{$expedition->logo_file_name}";
            $originalS3Path = config('config.uploads.expedition_logos_original').'/'.$filename;
            $mediumS3Path = config('config.uploads.expedition_logos_medium').'/'.$filename;

            if (! $isDryRun) {
                // Check if medium variant already exists on S3
                if (! Storage::disk('s3')->exists($mediumS3Path) && Storage::disk('s3')->exists($originalS3Path)) {
                    // Get original file content from S3
                    $fileContent = Storage::disk('s3')->get($originalS3Path);

                    // Create medium variant
                    $image = Image::read($fileContent);
                    $image->resize(318, 208, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $imageData = $image->encode();
                    Storage::disk('s3')->put($mediumS3Path, $imageData);

                    $this->line("   ✅ Created medium variant on S3: {$mediumS3Path}");
                }
            } else {
                $this->line("   Would create medium variant: {$mediumS3Path}");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Failed to create medium variant for {$expedition->title}: ".$e->getMessage());
        }
    }

    private function migrateProfileAvatars($isDryRun)
    {
        $this->info('📁 Migrating Profile avatars from paperclip to S3...');

        $profiles = Profile::whereNotNull('avatar_file_name')->get();
        $count = 0;

        foreach ($profiles as $profile) {
            if ($this->migratePaperclipFileToS3($profile, 'Profile', 'avatars', $profile->avatar_file_name, $isDryRun)) {

                // Also migrate or create variants
                $this->migrateOrCreateProfileVariants($profile, $isDryRun);
                $count++;
            }
        }

        $this->info("   Processed {$count} profile avatars");
    }

    private function migrateOrCreateProfileVariants($profile, $isDryRun)
    {
        try {
            $filename = "{$profile->id}_{$profile->avatar_file_name}";
            $originalS3Path = config('config.uploads.profile_avatars_original').'/'.$filename;
            $mediumS3Path = config('config.uploads.profile_avatars_medium').'/'.$filename;
            $smallS3Path = config('config.uploads.profile_avatars_small').'/'.$filename;

            if (! $isDryRun) {
                if (Storage::disk('s3')->exists($originalS3Path)) {
                    $fileContent = Storage::disk('s3')->get($originalS3Path);

                    // Create medium variant (160x160)
                    if (! Storage::disk('s3')->exists($mediumS3Path)) {
                        $this->createProfileVariant($fileContent, $mediumS3Path, 160, 160);
                        $this->line("   ✅ Created medium variant on S3: {$mediumS3Path}");
                    }

                    // Create small variant (25x25)
                    if (! Storage::disk('s3')->exists($smallS3Path)) {
                        $this->createProfileVariant($fileContent, $smallS3Path, 25, 25);
                        $this->line("   ✅ Created small variant on S3: {$smallS3Path}");
                    }
                }
            } else {
                $this->line("   Would create variants: {$mediumS3Path}, {$smallS3Path}");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Failed to create variants for Profile ID {$profile->id}: ".$e->getMessage());
        }
    }

    private function createProfileVariant($fileContent, $variantPath, $width, $height)
    {
        try {
            $image = Image::read($fileContent);
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $imageData = $image->encode();
            Storage::disk('s3')->put($variantPath, $imageData);
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Failed to create variant {$variantPath}: ".$e->getMessage());
        }
    }

    private function migrateProjectAssetDownloads($isDryRun)
    {
        $this->info('📁 Migrating Project Asset downloads from paperclip to S3...');

        $resources = ProjectAsset::whereNotNull('download_file_name')->get();
        $count = 0;

        foreach ($resources as $resource) {
            if ($this->migratePaperclipFileToS3($resource, 'ProjectAsset', 'downloads', $resource->download_file_name, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} project asset downloads");
    }

    private function migrateSiteAssetDocuments($isDryRun)
    {
        $this->info('📁 Migrating Resource documents from paperclip to S3...');

        $resources = SiteAsset::whereNotNull('document_file_name')->get();
        $count = 0;

        foreach ($resources as $resource) {
            if ($this->migratePaperclipFileToS3($resource, 'SiteAsset', 'documents', $resource->document_file_name, $isDryRun)) {
                $count++;
            }
        }

        $this->info("   Processed {$count} site-asset documents");
    }

    private function migratePaperclipFileToS3($model, $modelType, $attachment, $fileName, $isDryRun)
    {
        try {
            // Construct Paperclip path with id_partition
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $model->id);
            $paperclipPath = "paperclip/App/Models/{$modelType}/{$attachment}/{$idPartition}/original/{$fileName}";

            // Construct new S3 path based on config
            $s3Path = $this->getS3Path($modelType, $model->id, $fileName);

            // Check if Paperclip file exists
            if (! Storage::disk('public')->exists($paperclipPath)) {
                $this->warn("   ⚠️  Paperclip file not found: {$paperclipPath}");

                return false;
            }

            // Check if file already exists on S3
            if (Storage::disk('s3')->exists($s3Path)) {
                $this->line("   ⏭️  Already on S3: {$s3Path}");

                return false;
            }

            if ($isDryRun) {
                $this->line("   Would migrate: {$paperclipPath} → {$s3Path} (S3)");

                return true;
            }

            // Get file content from Paperclip location
            $fileContent = Storage::disk('public')->get($paperclipPath);

            // Upload to S3
            Storage::disk('s3')->put($s3Path, $fileContent);

            // Verify upload
            if (! Storage::disk('s3')->exists($s3Path)) {
                $this->error("   ❌ Failed to upload to S3: {$s3Path}");

                return false;
            }

            // Update model with new path
            $this->updateModelPath($model, $modelType, $s3Path);

            $this->line("   ✅ Migrated to S3: {$s3Path}");

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to migrate {$paperclipPath}: ".$e->getMessage());

            return false;
        }
    }

    private function getS3Path($modelType, $modelId, $fileName)
    {
        $newFileName = "{$modelId}_{$fileName}";

        switch ($modelType) {
            case 'Project':
                return config('config.uploads.project_logos').'/'.$newFileName;
            case 'Expedition':
                return config('config.uploads.expedition_logos_original').'/'.$newFileName;
            case 'Profile':
                return config('config.uploads.profile_avatars_original').'/'.$newFileName;
            case 'ProjectAsset':
                return config('config.uploads.project_resources_downloads').'/'.$newFileName;
            case 'SiteAsset':
                return config('config.uploads.resources').'/'.$newFileName;
            default:
                return 'uploads/general/'.$newFileName;
        }
    }

    private function updateModelPath($model, $modelType, $s3Path)
    {
        switch ($modelType) {
            case 'Project':
                $model->logo_path = $s3Path;
                break;
            case 'Expedition':
                $model->logo_path = $s3Path;
                break;
            case 'Profile':
                $model->avatar_path = $s3Path;
                break;
            case 'ProjectAsset':
                $model->download_path = $s3Path;
                break;
            case 'SiteAsset':
                $model->download_path = $s3Path;
                break;
        }

        $model->save();
    }
}
