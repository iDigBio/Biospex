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

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Information')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Asset Title'),
                        TextEntry::make('order')
                            ->label('Display Order')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('File Information')
                    ->schema([
                        TextEntry::make('download_path')
                            ->label('Filename')
                            ->formatStateUsing(fn (string $state): string => basename($state))
                            ->visible(fn ($record) => ! empty($record->download_path)),
                        TextEntry::make('download_path')
                            ->label('Download')
                            ->formatStateUsing(function ($record) {
                                if (empty($record->download_path)) {
                                    return 'No file';
                                }

                                // Generate signed URL for download
                                try {
                                    $url = \Storage::disk('s3')->url($record->download_path);
                                    $filename = basename($record->download_path);

                                    return '<a href="'.$url.'" target="_blank" class="text-primary hover:underline">
                                        <i class="fas fa-download mr-1"></i>Download '.$filename.'</a>';
                                } catch (\Exception $e) {
                                    return 'File not available';
                                }
                            })
                            ->html(),
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
