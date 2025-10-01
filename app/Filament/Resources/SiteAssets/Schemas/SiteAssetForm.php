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

namespace App\Filament\Resources\SiteAssets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Asset Title'),
                        Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->label('Description'),
                        TextInput::make('order')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(255)
                            ->label('Display Order')
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(2),

                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('download_path')
                            ->label('Asset File')
                            ->directory('site-assets')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'text/*', '.xlsx', '.xls', '.csv', '.zip', '.rar', '.doc', '.docx'])
                            ->maxSize(50000) // 50MB
                            ->helperText('Maximum file size: 50MB'),
                    ]),
            ]);
    }
}
