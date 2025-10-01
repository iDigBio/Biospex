<?php

namespace App\Filament\Resources\Metas;

use App\Filament\Resources\Metas\Pages\CreateMeta;
use App\Filament\Resources\Metas\Pages\EditMeta;
use App\Filament\Resources\Metas\Pages\ListMetas;
use App\Filament\Resources\Metas\Pages\ViewMeta;
use App\Filament\Resources\Metas\Schemas\MetaForm;
use App\Filament\Resources\Metas\Schemas\MetaInfolist;
use App\Filament\Resources\Metas\Tables\MetasTable;
use App\Models\Meta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MetaResource extends Resource
{
    protected static ?string $model = Meta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MetaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MetaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetasTable::configure($table);
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
            'index' => ListMetas::route('/'),
            'create' => CreateMeta::route('/create'),
            'view' => ViewMeta::route('/{record}'),
            'edit' => EditMeta::route('/{record}/edit'),
        ];
    }
}
