<div>
    <div class="form-group">
        <label for="file" class="form-label">{{ $fieldName ? ucfirst(str_replace('_', ' ', $fieldName)) : 'File' }}</label>
        <input type="file" 
               wire:model="file" 
               class="form-control @error('file') is-invalid @enderror"
               id="file"
               accept="{{ implode(',', array_map(fn($type) => '.' . $type, $allowedTypes)) }}">
        
        @error('file')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        
        <!-- Upload Progress Indicator -->
        <div wire:loading wire:target="file" class="mt-2">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 100%">
                    <i class="fas fa-cloud-upload-alt"></i> Uploading to S3...
                </div>
            </div>
        </div>
        
        <!-- File Selection Feedback -->
        @if($file)
            <div class="mt-2 text-success">
                <i class="fas fa-check-circle"></i>
                <small>File selected: {{ $file->getClientOriginalName() }}</small>
            </div>
        @endif
        
        <!-- Upload Success Message -->
        @if($uploadSuccess)
            <div class="mt-2 alert alert-success alert-sm">
                <i class="fas fa-cloud-check"></i>
                File uploaded successfully to S3!
            </div>
        @endif
        
        <!-- Upload Error Message -->
        @if($uploadError)
            <div class="mt-2 alert alert-danger alert-sm">
                <i class="fas fa-exclamation-triangle"></i>
                Upload failed: {{ $uploadError }}
            </div>
        @endif
        
        <small class="form-text text-muted">
            Max size: {{ $maxSize / 1024 }}MB. 
            Allowed types: {{ implode(', ', $allowedTypes) }}
            <br><small class="text-info">Files are stored in S3 bucket: {{ config('filesystems.disks.s3.bucket') }}</small>
        </small>
    </div>
</div>
