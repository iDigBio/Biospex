<?php

namespace App\Filament\Resources\GeoLocateForms;

use App\Filament\Resources\GeoLocateForms\Pages\CreateGeoLocateForm;
use App\Filament\Resources\GeoLocateForms\Pages\EditGeoLocateForm;
use App\Filament\Resources\GeoLocateForms\Pages\ListGeoLocateForms;
use App\Filament\Resources\GeoLocateForms\Pages\ViewGeoLocateForm;
use App\Filament\Resources\GeoLocateForms\Schemas\GeoLocateFormForm;
use App\Filament\Resources\GeoLocateForms\Schemas\GeoLocateFormInfolist;
use App\Filament\Resources\GeoLocateForms\Tables\GeoLocateFormsTable;
use App\Models\GeoLocateForm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeoLocateFormResource extends Resource
{
    protected static ?string $model = GeoLocateForm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GeoLocateFormForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeoLocateFormInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeoLocateFormsTable::configure($table);
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
            'index' => ListGeoLocateForms::route('/'),
            'create' => CreateGeoLocateForm::route('/create'),
            'view' => ViewGeoLocateForm::route('/{record}'),
            'edit' => EditGeoLocateForm::route('/{record}/edit'),
        ];
    }
}
