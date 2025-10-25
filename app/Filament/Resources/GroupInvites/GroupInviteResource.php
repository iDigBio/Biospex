<?php

namespace App\Filament\Resources\GroupInvites;

use App\Filament\Resources\GroupInvites\Pages\CreateGroupInvite;
use App\Filament\Resources\GroupInvites\Pages\EditGroupInvite;
use App\Filament\Resources\GroupInvites\Pages\ListGroupInvites;
use App\Filament\Resources\GroupInvites\Pages\ViewGroupInvite;
use App\Filament\Resources\GroupInvites\Schemas\GroupInviteForm;
use App\Filament\Resources\GroupInvites\Schemas\GroupInviteInfolist;
use App\Filament\Resources\GroupInvites\Tables\GroupInvitesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\GroupInvite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupInviteResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = GroupInvite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GroupInviteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupInviteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupInvitesTable::configure($table);
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
            'index' => ListGroupInvites::route('/'),
            'create' => CreateGroupInvite::route('/create'),
            'view' => ViewGroupInvite::route('/{record}'),
            'edit' => EditGroupInvite::route('/{record}/edit'),
        ];
    }
}
