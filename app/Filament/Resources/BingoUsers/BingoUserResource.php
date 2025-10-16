<?php

namespace App\Filament\Resources\BingoUsers;

use App\Filament\Resources\BingoUsers\Pages\CreateBingoUser;
use App\Filament\Resources\BingoUsers\Pages\EditBingoUser;
use App\Filament\Resources\BingoUsers\Pages\ListBingoUsers;
use App\Filament\Resources\BingoUsers\Pages\ViewBingoUser;
use App\Filament\Resources\BingoUsers\Schemas\BingoUserForm;
use App\Filament\Resources\BingoUsers\Schemas\BingoUserInfolist;
use App\Filament\Resources\BingoUsers\Tables\BingoUsersTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\BingoUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BingoUserResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = BingoUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BingoUserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BingoUserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BingoUsersTable::configure($table);
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
            'index' => ListBingoUsers::route('/'),
            'create' => CreateBingoUser::route('/create'),
            'view' => ViewBingoUser::route('/{record}'),
            'edit' => EditBingoUser::route('/{record}/edit'),
        ];
    }
}
