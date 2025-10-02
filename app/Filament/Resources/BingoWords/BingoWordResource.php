<?php

namespace App\Filament\Resources\BingoWords;

use App\Filament\Resources\BingoWords\Pages\CreateBingoWord;
use App\Filament\Resources\BingoWords\Pages\EditBingoWord;
use App\Filament\Resources\BingoWords\Pages\ListBingoWords;
use App\Filament\Resources\BingoWords\Pages\ViewBingoWord;
use App\Filament\Resources\BingoWords\Schemas\BingoWordForm;
use App\Filament\Resources\BingoWords\Schemas\BingoWordInfolist;
use App\Filament\Resources\BingoWords\Tables\BingoWordsTable;
use App\Models\BingoWord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BingoWordResource extends Resource
{
    protected static ?string $model = BingoWord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BingoWordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BingoWordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BingoWordsTable::configure($table);
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
            'index' => ListBingoWords::route('/'),
            'create' => CreateBingoWord::route('/create'),
            'view' => ViewBingoWord::route('/{record}'),
            'edit' => EditBingoWord::route('/{record}/edit'),
        ];
    }
}
