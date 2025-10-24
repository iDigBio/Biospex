<?php

namespace App\Filament\Resources\Headers;

use App\Filament\Resources\Headers\Pages\CreateHeader;
use App\Filament\Resources\Headers\Pages\EditHeader;
use App\Filament\Resources\Headers\Pages\ListHeaders;
use App\Filament\Resources\Headers\Pages\ViewHeader;
use App\Filament\Resources\Headers\Schemas\HeaderForm;
use App\Filament\Resources\Headers\Schemas\HeaderInfolist;
use App\Filament\Resources\Headers\Tables\HeadersTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\Header;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HeaderResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = Header::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HeaderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HeaderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeadersTable::configure($table);
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
            'index' => ListHeaders::route('/'),
            'create' => CreateHeader::route('/create'),
            'view' => ViewHeader::route('/{record}'),
            'edit' => EditHeader::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['project']);
    }
}
