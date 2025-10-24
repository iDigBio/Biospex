<?php

namespace App\Filament\Resources\GeoLocateForms\Pages;

use App\Filament\Resources\GeoLocateForms\GeoLocateFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeoLocateForms extends ListRecords
{
    protected static string $resource = GeoLocateFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
