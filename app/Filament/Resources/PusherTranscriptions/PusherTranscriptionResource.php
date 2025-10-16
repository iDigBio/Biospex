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

namespace App\Filament\Resources\PusherTranscriptions;

use App\Filament\Resources\PusherTranscriptions\Pages\CreatePusherTranscription;
use App\Filament\Resources\PusherTranscriptions\Pages\EditPusherTranscription;
use App\Filament\Resources\PusherTranscriptions\Pages\ListPusherTranscriptions;
use App\Filament\Resources\PusherTranscriptions\Pages\ViewPusherTranscription;
use App\Filament\Resources\PusherTranscriptions\Schemas\PusherTranscriptionForm;
use App\Filament\Resources\PusherTranscriptions\Schemas\PusherTranscriptionInfolist;
use App\Filament\Resources\PusherTranscriptions\Tables\PusherTranscriptionsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\PusherTranscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PusherTranscriptionResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = PusherTranscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PusherTranscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PusherTranscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PusherTranscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPusherTranscriptions::route('/'),
            'create' => CreatePusherTranscription::route('/create'),
            'view' => ViewPusherTranscription::route('/{record}'),
            'edit' => EditPusherTranscription::route('/{record}/edit'),
        ];
    }
}
