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

namespace App\Filament\Resources\ProjectAssets;

use App\Filament\Resources\ProjectAssets\Pages\CreateProjectAsset;
use App\Filament\Resources\ProjectAssets\Pages\EditProjectAsset;
use App\Filament\Resources\ProjectAssets\Pages\ListProjectAssets;
use App\Filament\Resources\ProjectAssets\Pages\ViewProjectAsset;
use App\Filament\Resources\ProjectAssets\Schemas\ProjectAssetForm;
use App\Filament\Resources\ProjectAssets\Schemas\ProjectAssetInfolist;
use App\Filament\Resources\ProjectAssets\Tables\ProjectAssetsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\ProjectAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectAssetResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = ProjectAsset::class;

    protected static ?string $modelLabel = 'Project Resources';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    public static function form(Schema $schema): Schema
    {
        return ProjectAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectAssetsTable::configure($table);
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
            'index' => ListProjectAssets::route('/'),
            'create' => CreateProjectAsset::route('/create'),
            'view' => ViewProjectAsset::route('/{record}'),
            'edit' => EditProjectAsset::route('/{record}/edit'),
        ];
    }
}
