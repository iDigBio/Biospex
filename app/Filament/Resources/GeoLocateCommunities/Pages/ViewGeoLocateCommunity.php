<?php

namespace App\Filament\Resources\GeoLocateCommunities\Pages;

use App\Filament\Resources\GeoLocateCommunities\GeoLocateCommunityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGeoLocateCommunity extends ViewRecord
{
    protected static string $resource = GeoLocateCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
