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

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PusherTranscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('json_editor')
                    ->label('MongoDB Document (JSON)')
                    ->helperText('Edit the complete MongoDB document. Changes will be saved directly to the database.')
                    ->rows(25)
                    ->extraAttributes([
                        'style' => 'font-family: "JetBrains Mono", "Fira Code", Consolas, monospace; font-size: 0.875rem; line-height: 1.5;',
                        'spellcheck' => 'false',
                    ])
                    ->afterStateHydrated(function ($component, $record) {
                        if (! $record) {
                            return;
                        }

                        $attributes = $record->toArray();
                        $component->state(json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    })
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
