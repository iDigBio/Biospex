<?php

namespace App\Filament\Resources\GeoLocateForms\Pages;

use App\Filament\Resources\GeoLocateForms\GeoLocateFormResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGeoLocateForm extends ViewRecord
{
    protected static string $resource = GeoLocateFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
