<?php

namespace App\Livewire;

use App\Services\Asset\ImageUploadService;

class ImageUpload extends FileUpload
{
    public $uploadSuccess = false;

    public $uploadError = null;

    protected ImageUploadService $imageUploadService;

    public function boot()
    {
        $this->imageUploadService = app(ImageUploadService::class);
    }

    public function mount($modelType = null, $fieldName = null, $maxSize = 10240, $allowedTypes = null, $projectUuid = null)
    {
        // Reset status flags
        $this->uploadSuccess = false;
        $this->uploadError = null;

        // Restrict to image types only
        $imageTypes = ['jpeg', 'jpg', 'png', 'gif'];

        parent::mount($modelType, $fieldName, $maxSize, $imageTypes, $projectUuid);
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

            // Use the unified ImageUploadService
            $originalPath = $this->imageUploadService->uploadImage(
                $this->file,
                $this->modelType,
                $this->storagePath
            );

            if (! $originalPath) {
                $this->uploadError = 'Upload failed';
                $this->uploading = false;

                return null;
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

    public function render()
    {
        return view('livewire.image-upload');
    }
}
