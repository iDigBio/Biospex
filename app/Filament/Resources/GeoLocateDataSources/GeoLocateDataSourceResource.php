<?php

namespace App\Filament\Resources\GeoLocateDataSources;

use App\Filament\Resources\GeoLocateDataSources\Pages\CreateGeoLocateDataSource;
use App\Filament\Resources\GeoLocateDataSources\Pages\EditGeoLocateDataSource;
use App\Filament\Resources\GeoLocateDataSources\Pages\ListGeoLocateDataSources;
use App\Filament\Resources\GeoLocateDataSources\Pages\ViewGeoLocateDataSource;
use App\Filament\Resources\GeoLocateDataSources\Schemas\GeoLocateDataSourceForm;
use App\Filament\Resources\GeoLocateDataSources\Schemas\GeoLocateDataSourceInfolist;
use App\Filament\Resources\GeoLocateDataSources\Tables\GeoLocateDataSourcesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\GeoLocateDataSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeoLocateDataSourceResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = GeoLocateDataSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GeoLocateDataSourceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeoLocateDataSourceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeoLocateDataSourcesTable::configure($table);
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
            'index' => ListGeoLocateDataSources::route('/'),
            'create' => CreateGeoLocateDataSource::route('/create'),
            'view' => ViewGeoLocateDataSource::route('/{record}'),
            'edit' => EditGeoLocateDataSource::route('/{record}/edit'),
        ];
    }
}
