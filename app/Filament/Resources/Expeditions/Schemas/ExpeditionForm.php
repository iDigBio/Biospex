<?php

namespace App\Filament\Resources\Expeditions\Schemas;

use App\Models\Project;
use App\Models\Workflow;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ExpeditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Expedition Title'),
                        Select::make('project_id')
                            ->label('Project')
                            ->options(Project::pluck('title', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('workflow_id')
                            ->label('Workflow')
                            ->options(Workflow::pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(3),

                Section::make('Description & Details')
                    ->schema([
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('keywords')
                            ->maxLength(255)
                            ->helperText('Comma-separated keywords for searching'),
                    ]),

                Section::make('Status & Settings')
                    ->schema([
                        Toggle::make('completed')
                            ->label('Expedition Completed')
                            ->default(false),
                        Toggle::make('locked')
                            ->label('Expedition Locked')
                            ->default(false)
                            ->helperText('Locked expeditions cannot be modified'),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Expedition Logo')
                            ->image()
                            ->disk('s3')
                            ->directory(config('config.uploads.expedition_logos').'/original') // Store in /original
                            ->visibility('private')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['318:208'])
                            ->imagePreviewHeight('150')
                            ->deletable()
                            ->downloadable()
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => time().'_'.$file->getClientOriginalName()
                            ),
                    ]),
            ]);
    }
}
