<?php

namespace App\Filament\Resources\EventUsers;

use App\Filament\Resources\EventUsers\Pages\CreateEventUser;
use App\Filament\Resources\EventUsers\Pages\EditEventUser;
use App\Filament\Resources\EventUsers\Pages\ListEventUsers;
use App\Filament\Resources\EventUsers\Pages\ViewEventUser;
use App\Filament\Resources\EventUsers\Schemas\EventUserForm;
use App\Filament\Resources\EventUsers\Schemas\EventUserInfolist;
use App\Filament\Resources\EventUsers\Tables\EventUsersTable;
use App\Models\EventUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventUserResource extends Resource
{
    protected static ?string $model = EventUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EventUserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventUserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventUsersTable::configure($table);
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
            'index' => ListEventUsers::route('/'),
            'create' => CreateEventUser::route('/create'),
            'view' => ViewEventUser::route('/{record}'),
            'edit' => EditEventUser::route('/{record}/edit'),
        ];
    }
}
