<?php

namespace App\Filament\Resources\Expeditions;

use App\Filament\Resources\Expeditions\Pages\CreateExpedition;
use App\Filament\Resources\Expeditions\Pages\EditExpedition;
use App\Filament\Resources\Expeditions\Pages\ListExpeditions;
use App\Filament\Resources\Expeditions\Pages\ViewExpedition;
use App\Filament\Resources\Expeditions\Schemas\ExpeditionForm;
use App\Filament\Resources\Expeditions\Schemas\ExpeditionInfolist;
use App\Filament\Resources\Expeditions\Tables\ExpeditionsTable;
use App\Models\Expedition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpeditionResource extends Resource
{
    protected static ?string $model = Expedition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ExpeditionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpeditionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpeditionsTable::configure($table);
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
            'index' => ListExpeditions::route('/'),
            'create' => CreateExpedition::route('/create'),
            'view' => ViewExpedition::route('/{record}'),
            'edit' => EditExpedition::route('/{record}/edit'),
        ];
    }
}
