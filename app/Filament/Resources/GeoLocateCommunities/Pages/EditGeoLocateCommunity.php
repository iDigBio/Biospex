<?php

namespace App\Filament\Resources\GeoLocateCommunities\Pages;

use App\Filament\Resources\GeoLocateCommunities\GeoLocateCommunityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeoLocateCommunity extends EditRecord
{
    protected static string $resource = GeoLocateCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
