<?php

namespace App\Filament\Resources\ActorContacts;

use App\Filament\Resources\ActorContacts\Pages\CreateActorContact;
use App\Filament\Resources\ActorContacts\Pages\EditActorContact;
use App\Filament\Resources\ActorContacts\Pages\ListActorContacts;
use App\Filament\Resources\ActorContacts\Pages\ViewActorContact;
use App\Filament\Resources\ActorContacts\Schemas\ActorContactForm;
use App\Filament\Resources\ActorContacts\Schemas\ActorContactInfolist;
use App\Filament\Resources\ActorContacts\Tables\ActorContactsTable;
use App\Models\ActorContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActorContactResource extends Resource
{
    protected static ?string $model = ActorContact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Actor Contact';

    public static function form(Schema $schema): Schema
    {
        return ActorContactForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActorContactInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActorContactsTable::configure($table);
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
            'index' => ListActorContacts::route('/'),
            'create' => CreateActorContact::route('/create'),
            'view' => ViewActorContact::route('/{record}'),
            'edit' => EditActorContact::route('/{record}/edit'),
        ];
    }
}
