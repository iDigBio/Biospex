<?php

namespace App\Filament\Resources\ActorExpeditions;

use App\Filament\Resources\ActorExpeditions\Pages\CreateActorExpedition;
use App\Filament\Resources\ActorExpeditions\Pages\EditActorExpedition;
use App\Filament\Resources\ActorExpeditions\Pages\ListActorExpeditions;
use App\Filament\Resources\ActorExpeditions\Pages\ViewActorExpedition;
use App\Filament\Resources\ActorExpeditions\Schemas\ActorExpeditionForm;
use App\Filament\Resources\ActorExpeditions\Schemas\ActorExpeditionInfolist;
use App\Filament\Resources\ActorExpeditions\Tables\ActorExpeditionsTable;
use App\Models\ActorExpedition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActorExpeditionResource extends Resource
{
    protected static ?string $model = ActorExpedition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ActorExpeditionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActorExpeditionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActorExpeditionsTable::configure($table);
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
            'index' => ListActorExpeditions::route('/'),
            'create' => CreateActorExpedition::route('/create'),
            'view' => ViewActorExpedition::route('/{record}'),
            'edit' => EditActorExpedition::route('/{record}/edit'),
        ];
    }
}
