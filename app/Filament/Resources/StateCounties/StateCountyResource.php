<?php

namespace App\Filament\Resources\StateCounties;

use App\Filament\Resources\StateCounties\Pages\CreateStateCounty;
use App\Filament\Resources\StateCounties\Pages\EditStateCounty;
use App\Filament\Resources\StateCounties\Pages\ListStateCounties;
use App\Filament\Resources\StateCounties\Pages\ViewStateCounty;
use App\Filament\Resources\StateCounties\Schemas\StateCountyForm;
use App\Filament\Resources\StateCounties\Schemas\StateCountyInfolist;
use App\Filament\Resources\StateCounties\Tables\StateCountiesTable;
use App\Models\StateCounty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StateCountyResource extends Resource
{
    protected static ?string $model = StateCounty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StateCountyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StateCountyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StateCountiesTable::configure($table);
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
            'index' => ListStateCounties::route('/'),
            'create' => CreateStateCounty::route('/create'),
            'view' => ViewStateCounty::route('/{record}'),
            'edit' => EditStateCounty::route('/{record}/edit'),
        ];
    }
}
