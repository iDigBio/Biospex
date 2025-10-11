<?php

namespace App\Livewire;

use App\Rules\FileUploadNameValidation;
use App\Services\Asset\AssetUploadService;
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

    protected AssetUploadService $assetUploadService;

    public function boot()
    {
        $this->assetUploadService = app(AssetUploadService::class);
    }

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
        $this->projectUuid = $projectUuid;

        // Use AssetUploadService configurations for asset types
        if (in_array($modelType, ['ProjectAsset', 'SiteAsset', 'Resource'])) {
            $this->maxSize = $maxSize ?: $this->assetUploadService->getMaxFileSize();

            if (! $allowedTypes) {
                // Convert service file types to extensions for validation
                $this->allowedTypes = ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'csv', 'zip', 'rar', 'jpeg', 'jpg', 'png', 'gif', 'txt'];
            } else {
                $this->allowedTypes = $allowedTypes;
            }
        } else {
            $this->maxSize = $maxSize;
            if ($allowedTypes) {
                $this->allowedTypes = $allowedTypes;
            }
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

            // Use the unified AssetUploadService for asset types
            if (in_array($this->modelType, ['ProjectAsset', 'SiteAsset', 'Resource'])) {
                // Map Resource type to SiteAsset for consistency
                $serviceModelType = $this->modelType === 'Resource' ? 'SiteAsset' : $this->modelType;
                $uploadedPath = $this->assetUploadService->uploadAsset(
                    $this->file,
                    $serviceModelType,
                    $this->storagePath
                );
            } else {
                // Fallback to original logic for other types (Project, Expedition, Profile)
                $disk = 's3';
                $filename = time().'_'.$this->file->getClientOriginalName();
                $uploadedPath = $this->file->storeAs($this->storagePath, $filename, $disk);

                // Verify file was uploaded to S3
                if (! Storage::disk('s3')->exists($uploadedPath)) {
                    throw new \Exception('Failed to upload file to S3');
                }
            }

            if (! $uploadedPath) {
                $this->uploadError = 'Upload failed';
                $this->uploading = false;

                return null;
            }

            // Set success flag
            $this->uploadSuccess = true;

            // Emit event with the uploaded file path for parent form integration
            $this->dispatch('fileUploaded', [
                'fieldName' => $this->fieldName,
                'filePath' => $uploadedPath,
                'modelType' => $this->modelType,
            ]);

            // Reset file input
            $this->reset('file');

            $this->uploading = false;

            return $uploadedPath;

        } catch (\Exception $e) {
            $this->uploadError = 'Upload failed: '.$e->getMessage();
            $this->uploading = false;

            return null;
        }
    }

    protected function getStoragePath()
    {
        // Use AssetUploadService for asset types
        if (in_array($this->modelType, ['ProjectAsset', 'SiteAsset'])) {
            // Map Resource type to SiteAsset for consistency
            $modelType = $this->modelType === 'Resource' ? 'SiteAsset' : $this->modelType;

            return $this->assetUploadService->getStoragePath($modelType);
        }

        // Legacy paths for non-asset types
        $paths = [
            'Project' => config('config.uploads.project_logos'),
            'Expedition' => config('config.uploads.expedition_logos'),
            'Profile' => config('config.uploads.profile_avatars'),
        ];

        return $paths[$this->modelType] ?? 'uploads/general';
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
