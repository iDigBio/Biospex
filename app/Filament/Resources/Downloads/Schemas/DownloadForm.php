<?php

namespace App\Filament\Resources\Downloads\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class DownloadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                Select::make('actor_id')
                    ->relationship('actor', 'title')
                    ->required(),
                Select::make('type')
                    ->options([
                        'classification' => 'Classification',
                        'reconciled' => 'Reconciled',
                        'reconciled-with-expert' => 'Reconciled with Expert',
                        'reconciled-with-user' => 'Reconciled with User',
                        'summary' => 'Summary',
                        'transcript' => 'Transcript',
                        'explained' => 'Explained',
                        'lambda-reconciliation' => 'Lambda Reconciliation',
                        'lambda-ocr' => 'Lambda OCR',
                        'report' => 'Report',
                    ])
                    ->required()
                    ->reactive(),
                FileUpload::make('file')
                    ->required()
                    ->disk('s3')
                    ->directory(function (callable $get) {
                        $type = $get('type');
                        if (! $type) {
                            return config('zooniverse.directory.parent', 'zooniverse');
                        }

                        return config("zooniverse.directory.{$type}", config('zooniverse.directory.parent', 'zooniverse'));
                    })
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                        $type = $get('type');
                        $expeditionId = $get('expedition_id');

                        if ($type === 'report') {
                            // Generate random string for report files
                            $randomString = bin2hex(random_bytes(8));

                            return $randomString.'.csv';
                        } elseif ($expeditionId) {
                            // Use expedition ID for other CSV files
                            return $expeditionId.'.csv';
                        }

                        // Fallback to original filename if no expedition ID
                        return $file->getClientOriginalName();
                    })
                    ->afterStateHydrated(function (FileUpload $component, $state, $record) {
                        if ($record && $record->file && $record->type) {
                            // Build the full S3 path for the existing file
                            $directory = config("zooniverse.directory.{$record->type}", config('zooniverse.directory.parent', 'zooniverse'));
                            $filePath = $directory.'/'.$record->file;

                            // Check if file exists on S3 and set the state to the file path
                            if (Storage::disk('s3')->exists($filePath)) {
                                // Set the state to the file path for display purposes
                                $component->state([$filePath]);
                            }
                        }
                    })
                    ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain', 'application/zip', 'application/x-zip-compressed'])
                    ->maxSize(200000) // 200MB
                    ->uploadingMessage('Uploading file...')
                    ->panelLayout('compact'),
            ]);
    }
}
