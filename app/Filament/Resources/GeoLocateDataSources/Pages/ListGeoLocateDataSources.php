<?php

namespace App\Filament\Resources\GeoLocateDataSources\Pages;

use App\Filament\Resources\GeoLocateDataSources\GeoLocateDataSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeoLocateDataSources extends ListRecords
{
    protected static string $resource = GeoLocateDataSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
