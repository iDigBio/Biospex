<?php

namespace App\Observers;

use App\Models\Expedition;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ExpeditionObserver
{
    public function saved(Expedition $expedition)
    {
        // Check if logo_path was updated and contains '/original/'
        if ($expedition->wasChanged('logo_path') &&
            $expedition->logo_path &&
            str_contains($expedition->logo_path, '/original/')) {

            $this->createMediumVariant($expedition);
        }
    }

    protected function createMediumVariant(Expedition $expedition): void
    {
        try {
            $originalPath = $expedition->logo_path;
            $mediumPath = str_replace('/original/', '/medium/', $originalPath);

            // Check if medium variant already exists
            if (Storage::disk('s3')->exists($mediumPath)) {
                return;
            }

            // Get original file content from S3
            $fileContent = Storage::disk('s3')->get($originalPath);

            // Create medium variant using the same syntax as ImageUpload.php
            $image = Image::read($fileContent);

            // Resize image maintaining aspect ratio (matching your ImageUpload.php)
            $image->resize(318, 208);

            // Store medium variant on S3
            $imageData = $image->encode();
            Storage::disk('s3')->put($mediumPath, $imageData);

        } catch (\Exception $e) {
            logger()->error('Failed to create expedition medium variant: '.$e->getMessage());
        }
    }
}
