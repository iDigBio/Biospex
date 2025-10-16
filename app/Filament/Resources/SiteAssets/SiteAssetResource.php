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

namespace App\Filament\Resources\SiteAssets;

use App\Filament\Resources\SiteAssets\Pages\CreateSiteAsset;
use App\Filament\Resources\SiteAssets\Pages\EditSiteAsset;
use App\Filament\Resources\SiteAssets\Pages\ListSiteAssets;
use App\Filament\Resources\SiteAssets\Pages\ViewSiteAsset;
use App\Filament\Resources\SiteAssets\Schemas\SiteAssetForm;
use App\Filament\Resources\SiteAssets\Schemas\SiteAssetInfolist;
use App\Filament\Resources\SiteAssets\Tables\SiteAssetsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\SiteAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiteAssetResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = SiteAsset::class;

    protected static ?string $modelLabel = 'Resources';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    public static function form(Schema $schema): Schema
    {
        return SiteAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteAssetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteAssets::route('/'),
            'create' => CreateSiteAsset::route('/create'),
            'view' => ViewSiteAsset::route('/{record}'),
            'edit' => EditSiteAsset::route('/{record}/edit'),
        ];
    }
}
