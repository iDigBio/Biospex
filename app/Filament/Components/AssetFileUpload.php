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

namespace App\Filament\Components;

use App\Services\Asset\AssetUploadService;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssetFileUpload extends FileUpload
{
    protected string $modelType;

    protected string $baseStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Always use S3 disk
        $this->disk('s3');

        // Set up accepted file types from service
        $assetUploadService = app(AssetUploadService::class);
        $this->acceptedFileTypes($assetUploadService->getSupportedFileTypes());

        // Set max file size from service
        $this->maxSize($assetUploadService->getMaxFileSize());

        // Set up custom file name handling
        $this->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
            return time().'_'.$file->getClientOriginalName();
        });

        // Set up file processing with unified service
        $this->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $component) {
            return $component->handleAssetUpload($file);
        });

        // Set up file deletion with unified service
        $this->deleteUploadedFileUsing(function ($file, $component) {
            return $component->handleAssetDeletion($file);
        });
    }

    /**
     * Set the model type for upload handling
     */
    public function modelType(string $modelType): static
    {
        $this->modelType = $modelType;

        // Set directory from service
        $assetUploadService = app(AssetUploadService::class);
        $this->directory($assetUploadService->getStoragePath($modelType));

        return $this;
    }

    /**
     * Set the base storage path
     */
    public function baseDirectory(string $path): static
    {
        $this->baseStoragePath = $path;
        $this->directory($path);

        return $this;
    }

    /**
     * Handle asset upload using the unified service
     */
    protected function handleAssetUpload(TemporaryUploadedFile $file): string
    {
        $assetUploadService = app(AssetUploadService::class);

        // Get current file path for replacement
        $currentFilePath = $this->getState();

        $uploadedPath = $assetUploadService->uploadAsset(
            $file,
            $this->modelType,
            $this->baseStoragePath,
            $currentFilePath
        );

        if (! $uploadedPath) {
            throw new \Exception('Asset upload failed');
        }

        return $uploadedPath;
    }

    /**
     * Handle asset deletion using the unified service
     */
    protected function handleAssetDeletion(string $filePath): bool
    {
        try {
            $assetUploadService = app(AssetUploadService::class);
            $assetUploadService->deleteAsset($filePath);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a new AssetFileUpload instance for ProjectAssets
     */
    public static function makeForProjectAsset(string $name): static
    {
        return static::make($name)
            ->baseDirectory(config('config.uploads.project-assets'))
            ->modelType('ProjectAsset');
    }

    /**
     * Create a new AssetFileUpload instance for SiteAssets
     */
    public static function makeForSiteAsset(string $name): static
    {
        return static::make($name)
            ->baseDirectory(config('config.uploads.site-assets'))
            ->modelType('SiteAsset');
    }
}
