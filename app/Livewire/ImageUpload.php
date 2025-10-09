<?php

namespace App\Livewire;

use Intervention\Image\Laravel\Facades\Image;
use Storage;

class ImageUpload extends FileUpload
{
    public $variants = [];

    public $uploadSuccess = false;

    public $uploadError = null;

    public function mount($modelType = null, $fieldName = null, $maxSize = 10240, $allowedTypes = null, $projectUuid = null)
    {
        // Reset status flags
        $this->uploadSuccess = false;
        $this->uploadError = null;

        // Restrict to image types only
        $imageTypes = ['jpeg', 'jpg', 'png', 'gif'];

        parent::mount($modelType, $fieldName, $maxSize, $imageTypes, $projectUuid);

        // Set variants based on model type
        $this->setVariants();
    }

    protected function setVariants()
    {
        $variantConfigs = [
            'Profile' => [
                'medium' => ['width' => 160, 'height' => 160],
                'small' => ['width' => 25, 'height' => 25],
            ],
            'Expedition' => [
                'medium' => ['width' => 318, 'height' => 208],
            ],
        ];

        $this->variants = $variantConfigs[$this->modelType] ?? [];
    }

    public function save()
    {
        try {
            $this->uploading = true;
            $this->uploadSuccess = false;
            $this->uploadError = null;

            $this->validate();

            if (! $this->file) {
                $this->uploadError = 'No file selected';
                $this->uploading = false;

                return null;
            }

            // Always use S3 for file uploads regardless of default filesystem
            $disk = 's3';
            $filename = time().'_'.$this->file->getClientOriginalName();

            // Store original image on S3 - use 'original' subdirectory if variants are configured
            $originalStoragePath = ! empty($this->variants) ? $this->storagePath.'/original' : $this->storagePath;
            $originalPath = $this->file->storeAs($originalStoragePath, $filename, $disk);

            // Verify file was uploaded to S3
            if (! Storage::disk('s3')->exists($originalPath)) {
                $this->uploadError = 'Failed to upload file to S3';

                return null;
            }

            // Create variants if configured
            if (! empty($this->variants)) {
                $this->createVariants($filename, $disk);
            }

            // Set success flag
            $this->uploadSuccess = true;

            // Emit event with the uploaded file path for parent form integration
            $this->dispatch('fileUploaded', [
                'fieldName' => $this->fieldName,
                'filePath' => $originalPath,
                'modelType' => $this->modelType,
            ]);

            // Reset file input
            $this->reset('file');

            $this->uploading = false;

            return $originalPath;

        } catch (\Exception $e) {
            $this->uploadError = 'Upload failed: '.$e->getMessage();
            $this->uploading = false;

            return null;
        }
    }

    public function updatedFile()
    {
        if ($this->file) {
            // Reset previous status
            $this->uploadSuccess = false;
            $this->uploadError = null;

            // Auto-save when file is selected
            $this->save();
        }
    }

    protected function createVariants($filename, $disk)
    {
        foreach ($this->variants as $variant => $dimensions) {
            try {
                // Create intervention image from uploaded file
                $image = Image::read($this->file->getRealPath());

                // Resize image maintaining aspect ratio
                $image->resize($dimensions['width'], $dimensions['height']);

                // Create variant path
                $variantPath = str_replace('/'.basename($this->storagePath), '/'.basename($this->storagePath).'/'.$variant, $this->storagePath);
                $variantFullPath = $variantPath.'/'.$filename;

                // Encode image and store
                $imageData = $image->encode();
                Storage::disk($disk)->put($variantFullPath, $imageData);

            } catch (\Exception $e) {
                // Don't fail upload for variant creation errors
            }
        }
    }

    public function render()
    {
        return view('livewire.image-upload');
    }
}
