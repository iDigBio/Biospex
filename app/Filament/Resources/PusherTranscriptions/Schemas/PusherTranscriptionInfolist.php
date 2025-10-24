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

namespace App\Filament\Resources\PusherTranscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PusherTranscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('json_document')
                    ->label('MongoDB Document')
                    ->getStateUsing(function ($record) {
                        $attributes = $record->toArray();

                        return json_encode($this->convertObjectIdsToStrings($attributes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    })
                    ->extraAttributes([
                        'style' => 'white-space: pre; font-family: "JetBrains Mono", "Fira Code", Consolas, monospace; background: #f8f9fa; border: 1px solid #e9ecef; padding: 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; line-height: 1.6; overflow-x: auto;',
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private function convertObjectIdsToStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value) && get_class($value) === 'MongoDB\BSON\ObjectId') {
                $data[$key] = (string) $value;
            } elseif (is_array($value)) {
                $data[$key] = $this->convertObjectIdsToStrings($value);
            }
        }

        return $data;
    }
}
