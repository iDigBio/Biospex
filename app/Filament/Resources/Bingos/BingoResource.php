<?php

namespace App\Filament\Resources\Bingos;

use App\Filament\Resources\Bingos\Pages\CreateBingo;
use App\Filament\Resources\Bingos\Pages\EditBingo;
use App\Filament\Resources\Bingos\Pages\ListBingos;
use App\Filament\Resources\Bingos\Pages\ViewBingo;
use App\Filament\Resources\Bingos\Schemas\BingoForm;
use App\Filament\Resources\Bingos\Schemas\BingoInfolist;
use App\Filament\Resources\Bingos\Tables\BingosTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\Bingo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BingoResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = Bingo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BingoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BingoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BingosTable::configure($table);
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
            'index' => ListBingos::route('/'),
            'create' => CreateBingo::route('/create'),
            'view' => ViewBingo::route('/{record}'),
            'edit' => EditBingo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['user.profile', 'project']);
    }
}
