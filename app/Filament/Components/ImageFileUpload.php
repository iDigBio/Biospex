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

use App\Services\Asset\ImageUploadService;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageFileUpload extends FileUpload
{
    protected string $modelType;

    protected string $baseStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Always use S3 disk
        $this->disk('s3');

        // Restrict to image types
        $this->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);

        // Set up custom file name handling
        $this->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
            return time().'_'.$file->getClientOriginalName();
        });

        // Set up file processing with unified service
        $this->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $component) {
            return $component->handleImageUpload($file);
        });

        // Set up file deletion with unified service
        $this->deleteUploadedFileUsing(function ($file, $component) {
            return $component->handleImageDeletion($file);
        });
    }

    /**
     * Set the model type for variant creation
     */
    public function modelType(string $modelType): static
    {
        $this->modelType = $modelType;

        $imageUploadService = app(ImageUploadService::class);

        // Set directory with /original suffix if variants are required
        if ($imageUploadService->requiresVariants($modelType)) {
            $this->directory($this->baseStoragePath.'/original');
        } else {
            $this->directory($this->baseStoragePath);
        }

        return $this;
    }

    /**
     * Set the base storage path
     */
    public function baseDirectory(string $path): static
    {
        $this->baseStoragePath = $path;

        return $this;
    }

    /**
     * Handle image upload using the unified service
     */
    protected function handleImageUpload(TemporaryUploadedFile $file): string
    {
        $imageUploadService = app(ImageUploadService::class);

        // Get current file path for replacement
        $currentFilePath = $this->getState();

        $uploadedPath = $imageUploadService->uploadImage(
            $file,
            $this->modelType,
            $this->baseStoragePath,
            $currentFilePath
        );

        if (! $uploadedPath) {
            throw new \Exception('Image upload failed');
        }

        return $uploadedPath;
    }

    /**
     * Handle image deletion using the unified service
     */
    protected function handleImageDeletion(string $filePath): bool
    {
        try {
            $imageUploadService = app(ImageUploadService::class);
            $imageUploadService->deleteImage($filePath, $this->modelType);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a new ImageFileUpload instance for Projects
     */
    public static function makeForProject(string $name): static
    {
        return static::make($name)
            ->baseDirectory(config('config.uploads.project_logos'))
            ->modelType('Project');
    }

    /**
     * Create a new ImageFileUpload instance for Expeditions
     */
    public static function makeForExpedition(string $name): static
    {
        return static::make($name)
            ->baseDirectory(config('config.uploads.expedition_logos'))
            ->modelType('Expedition');
    }

    /**
     * Create a new ImageFileUpload instance for Profiles
     */
    public static function makeForProfile(string $name): static
    {
        return static::make($name)
            ->baseDirectory(config('config.uploads.profile_avatars'))
            ->modelType('Profile');
    }
}
