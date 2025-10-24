<?php

namespace App\Filament\Resources\Expeditions\Schemas;

use App\Filament\Components\ImageFileUpload;
use App\Models\Project;
use App\Models\Workflow;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpeditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expedition Details')
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
                            ->nullable()
                            ->extraAttributes(['style' => 'max-width: 33.333%;']),
                        Textarea::make('description')
                            ->required()
                            ->rows(4),
                        TextInput::make('keywords')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Comma-separated keywords for searching'),
                        ImageFileUpload::makeForExpedition('logo_path')
                            ->label('Expedition Logo')
                            ->image()
                            ->visibility('private')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['318:208'])
                            ->imagePreviewHeight('150')
                            ->deletable()
                            ->downloadable()
                            ->maxSize(2048),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
