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

use App\Models\Project;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                            ->options([
                                'Dataset' => 'Dataset',
                                'Documentation' => 'Documentation',
                                'Image' => 'Image',
                                'Video' => 'Video',
                                'Other' => 'Other',
                            ])
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Asset Name'),
                        Textarea::make('description')
                            ->maxLength(255)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('download_path')
                            ->label('Asset File')
                            ->directory('project-assets')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'text/*', '.xlsx', '.xls', '.csv', '.zip', '.rar'])
                            ->maxSize(50000) // 50MB
                            ->helperText('Maximum file size: 50MB'),
                    ]),
            ]);
    }
}
