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

namespace App\Filament\Resources\ProjectAssets\Schemas;

use App\Filament\Components\AssetFileUpload;
use App\Models\Project;
use App\Services\Validation\AssetValidationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Information')
                    ->schema([
                        Select::make('project_id')
                            ->label('Project')
                            ->options(Project::pluck('title', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->options(function () {
                                $projectAssets = config('config.project_assets', []);

                                return array_combine($projectAssets, $projectAssets);
                            })
                            ->required()
                            ->live()
                            ->helperText('Select the asset type to determine required fields'),
                        TextInput::make('name')
                            ->label('Asset Name / URL')
                            ->required()
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $type = $get('type');
                                        if (! $type) {
                                            return;
                                        }

                                        $assetValidationService = app(AssetValidationService::class);
                                        $errors = $assetValidationService->validateAssetType($type, $value);

                                        foreach ($errors as $error) {
                                            if (str_contains($error, 'URL') || str_contains($error, 'Name')) {
                                                $fail($error);
                                            }
                                        }
                                    };
                                },
                            ])
                            ->helperText('For Website/Video URL types: enter valid URL. For other types: enter descriptive name')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->required()
                            ->maxLength(255)
                            ->rows(3)
                            ->helperText('Provide a detailed description of this asset')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('File Upload')
                    ->schema([
                        AssetFileUpload::makeForProjectAsset('download_path')
                            ->label('Asset File')
                            ->visibility('private')
                            ->visible(fn ($get) => $get('type') === 'File Download')
                            ->required(fn ($get) => $get('type') === 'File Download')
                            ->helperText('Required for "File Download" type. Maximum size: 10MB. Accepted types: txt, doc, csv, pdf'),
                    ]),
            ]);
    }
}
