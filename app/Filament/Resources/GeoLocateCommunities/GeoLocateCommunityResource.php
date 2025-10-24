<?php

namespace App\Filament\Resources\GeoLocateCommunities;

use App\Filament\Resources\GeoLocateCommunities\Pages\CreateGeoLocateCommunity;
use App\Filament\Resources\GeoLocateCommunities\Pages\EditGeoLocateCommunity;
use App\Filament\Resources\GeoLocateCommunities\Pages\ListGeoLocateCommunities;
use App\Filament\Resources\GeoLocateCommunities\Pages\ViewGeoLocateCommunity;
use App\Filament\Resources\GeoLocateCommunities\Schemas\GeoLocateCommunityForm;
use App\Filament\Resources\GeoLocateCommunities\Schemas\GeoLocateCommunityInfolist;
use App\Filament\Resources\GeoLocateCommunities\Tables\GeoLocateCommunitiesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\GeoLocateCommunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeoLocateCommunityResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = GeoLocateCommunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GeoLocateCommunityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeoLocateCommunityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeoLocateCommunitiesTable::configure($table);
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
            'index' => ListGeoLocateCommunities::route('/'),
            'create' => CreateGeoLocateCommunity::route('/create'),
            'view' => ViewGeoLocateCommunity::route('/{record}'),
            'edit' => EditGeoLocateCommunity::route('/{record}/edit'),
        ];
    }
}
