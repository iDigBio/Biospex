<?php

namespace App\Filament\Resources\GeoLocateCommunities\Pages;

use App\Filament\Resources\GeoLocateCommunities\GeoLocateCommunityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeoLocateCommunities extends ListRecords
{
    protected static string $resource = GeoLocateCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
