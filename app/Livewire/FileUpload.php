<?php

namespace App\Livewire;

use App\Rules\FileUploadNameValidation;
use Livewire\Component;
use Livewire\WithFileUploads;
use Storage;

class FileUpload extends Component
{
    use WithFileUploads;

    public $file;

    public $modelType;

    public $fieldName;

    public $maxSize = 10240; // 10MB default

    public $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx'];

    public $storagePath;

    public $projectUuid;

    public $uploadSuccess = false;

    public $uploadError = null;

    public $uploading = false;

    protected function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.$this->maxSize,
                'mimes:'.implode(',', $this->allowedTypes),
                new FileUploadNameValidation,
            ],
        ];
    }

    public function mount($modelType = null, $fieldName = null, $maxSize = 10240, $allowedTypes = null, $projectUuid = null)
    {
        // Reset status flags
        $this->uploadSuccess = false;
        $this->uploadError = null;

        $this->modelType = $modelType;
        $this->fieldName = $fieldName;
        $this->maxSize = $maxSize;
        $this->projectUuid = $projectUuid;

        if ($allowedTypes) {
            $this->allowedTypes = $allowedTypes;
        }

        // Set storage path based on model type
        $this->storagePath = $this->getStoragePath();
    }

    public function updatedFile()
    {
        if ($this->file) {
            // Reset previous status
            $this->uploadSuccess = false;
            $this->uploadError = null;

            // Auto-save when file is selected (same as ImageUpload)
            $this->save();
        }
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

            // Store the file on S3
            $storedPath = $this->file->storeAs($this->storagePath, $filename, $disk);

            // Verify file was uploaded to S3
            if (! Storage::disk('s3')->exists($storedPath)) {
                $this->uploadError = 'Failed to upload file to S3';

                return null;
            }

            // Set success flag
            $this->uploadSuccess = true;

            // Emit event with the uploaded file path for parent form integration
            $this->dispatch('fileUploaded', [
                'fieldName' => $this->fieldName,
                'filePath' => $storedPath,
                'modelType' => $this->modelType,
            ]);

            // Reset file input
            $this->reset('file');

            $this->uploading = false;

            return $storedPath;

        } catch (\Exception $e) {
            $this->uploadError = 'Upload failed: '.$e->getMessage();
            $this->uploading = false;

            return null;
        }
    }

    protected function getStoragePath()
    {
        $paths = [
            'Project' => config('config.uploads.project_logos'),
            'Expedition' => config('config.uploads.expedition_logos'),
            'Profile' => config('config.uploads.profile_avatars'),
            'ProjectResource' => $this->getProjectResourcePath(),
            'Resource' => config('config.uploads.resources'),
        ];

        return $paths[$this->modelType] ?? 'uploads/general';
    }

    protected function getProjectResourcePath()
    {
        if ($this->projectUuid) {
            return config('config.uploads.project_resources_base').'/'.$this->projectUuid;
        }

        // Fallback to original path if no project UUID is provided
        return config('config.uploads.project_resources_downloads');
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
