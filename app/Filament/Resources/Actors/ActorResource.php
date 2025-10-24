<?php

namespace App\Filament\Resources\Actors;

use App\Filament\Resources\Actors\Pages\CreateActor;
use App\Filament\Resources\Actors\Pages\EditActor;
use App\Filament\Resources\Actors\Pages\ListActors;
use App\Filament\Resources\Actors\Pages\ViewActor;
use App\Filament\Resources\Actors\Schemas\ActorForm;
use App\Filament\Resources\Actors\Schemas\ActorInfolist;
use App\Filament\Resources\Actors\Tables\ActorsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\Actor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActorResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = Actor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ActorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActorsTable::configure($table);
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
            'index' => ListActors::route('/'),
            'create' => CreateActor::route('/create'),
            'view' => ViewActor::route('/{record}'),
            'edit' => EditActor::route('/{record}/edit'),
        ];
    }
}
