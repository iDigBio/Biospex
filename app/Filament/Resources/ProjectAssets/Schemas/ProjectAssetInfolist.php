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

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Information')
                    ->schema([
                        TextEntry::make('project.title')
                            ->label('Project'),
                        TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Dataset' => 'success',
                                'Documentation' => 'info',
                                'Image' => 'warning',
                                'Video' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('name')
                            ->label('Asset Name'),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('File Information')
                    ->schema([
                        TextEntry::make('download_path')
                            ->label('File Path')
                            ->copyable()
                            ->copyMessage('File path copied!')
                            ->visible(fn ($record) => ! empty($record->download_path)),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
