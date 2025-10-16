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

namespace App\Filament\Resources\GeoLocateExports;

use App\Filament\Resources\GeoLocateExports\Pages\CreateGeoLocateExport;
use App\Filament\Resources\GeoLocateExports\Pages\EditGeoLocateExport;
use App\Filament\Resources\GeoLocateExports\Pages\ListGeoLocateExports;
use App\Filament\Resources\GeoLocateExports\Pages\ViewGeoLocateExport;
use App\Filament\Resources\GeoLocateExports\Schemas\GeoLocateExportForm;
use App\Filament\Resources\GeoLocateExports\Schemas\GeoLocateExportInfolist;
use App\Filament\Resources\GeoLocateExports\Tables\GeoLocateExportsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\GeoLocateExport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeoLocateExportResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = GeoLocateExport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GeoLocateExportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeoLocateExportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeoLocateExportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeoLocateExports::route('/'),
            'create' => CreateGeoLocateExport::route('/create'),
            'view' => ViewGeoLocateExport::route('/{record}'),
            'edit' => EditGeoLocateExport::route('/{record}/edit'),
        ];
    }
}
