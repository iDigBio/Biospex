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

namespace App\Filament\Resources\ActorWorkflows;

use App\Filament\Resources\ActorWorkflows\Pages\CreateActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Pages\EditActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Pages\ListActorWorkflows;
use App\Filament\Resources\ActorWorkflows\Pages\ViewActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Schemas\ActorWorkflowForm;
use App\Filament\Resources\ActorWorkflows\Schemas\ActorWorkflowInfolist;
use App\Filament\Resources\ActorWorkflows\Tables\ActorWorkflowsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\ActorWorkflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActorWorkflowResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = ActorWorkflow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ActorWorkflowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActorWorkflowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActorWorkflowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActorWorkflows::route('/'),
            'create' => CreateActorWorkflow::route('/create'),
            'view' => ViewActorWorkflow::route('/{record}'),
            'edit' => EditActorWorkflow::route('/{record}/edit'),
        ];
    }
}
