<?php

namespace App\Filament\Resources\Updates;

use App\Filament\Resources\Updates\Pages\CreateUpdate;
use App\Filament\Resources\Updates\Pages\EditUpdate;
use App\Filament\Resources\Updates\Pages\ListUpdates;
use App\Filament\Resources\Updates\Pages\ViewUpdate;
use App\Filament\Resources\Updates\Schemas\UpdateForm;
use App\Filament\Resources\Updates\Schemas\UpdateInfolist;
use App\Filament\Resources\Updates\Tables\UpdatesTable;
use App\Models\Update;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UpdateResource extends Resource
{
    protected static ?string $model = Update::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UpdateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UpdateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UpdatesTable::configure($table);
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
            'index' => ListUpdates::route('/'),
            'create' => CreateUpdate::route('/create'),
            'view' => ViewUpdate::route('/{record}'),
            'edit' => EditUpdate::route('/{record}/edit'),
        ];
    }
}
