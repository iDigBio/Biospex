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

namespace App\Filament\Resources\Reconciles;

use App\Filament\Resources\Reconciles\Pages\CreateReconcile;
use App\Filament\Resources\Reconciles\Pages\EditReconcile;
use App\Filament\Resources\Reconciles\Pages\ListReconciles;
use App\Filament\Resources\Reconciles\Pages\ViewReconcile;
use App\Filament\Resources\Reconciles\Schemas\ReconcileForm;
use App\Filament\Resources\Reconciles\Schemas\ReconcileInfolist;
use App\Filament\Resources\Reconciles\Tables\ReconcilesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\Reconcile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReconcileResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = Reconcile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReconcileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReconcileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReconcilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReconciles::route('/'),
            'create' => CreateReconcile::route('/create'),
            'view' => ViewReconcile::route('/{record}'),
            'edit' => EditReconcile::route('/{record}/edit'),
        ];
    }
}
