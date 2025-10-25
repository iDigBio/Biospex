<?php

namespace App\Filament\Resources\GeoLocateDataSources\Pages;

use App\Filament\Resources\GeoLocateDataSources\GeoLocateDataSourceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGeoLocateDataSource extends ViewRecord
{
    protected static string $resource = GeoLocateDataSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
