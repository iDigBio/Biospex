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

namespace App\Filament\Resources\PanoptesTranscriptions;

use App\Filament\Resources\PanoptesTranscriptions\Pages\CreatePanoptesTranscription;
use App\Filament\Resources\PanoptesTranscriptions\Pages\EditPanoptesTranscription;
use App\Filament\Resources\PanoptesTranscriptions\Pages\ListPanoptesTranscriptions;
use App\Filament\Resources\PanoptesTranscriptions\Pages\ViewPanoptesTranscription;
use App\Filament\Resources\PanoptesTranscriptions\Schemas\PanoptesTranscriptionForm;
use App\Filament\Resources\PanoptesTranscriptions\Schemas\PanoptesTranscriptionInfolist;
use App\Filament\Resources\PanoptesTranscriptions\Tables\PanoptesTranscriptionsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\PanoptesTranscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PanoptesTranscriptionResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = PanoptesTranscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PanoptesTranscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PanoptesTranscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanoptesTranscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPanoptesTranscriptions::route('/'),
            'create' => CreatePanoptesTranscription::route('/create'),
            'view' => ViewPanoptesTranscription::route('/{record}'),
            'edit' => EditPanoptesTranscription::route('/{record}/edit'),
        ];
    }
}
