<?php

namespace App\Filament\Resources\GeoLocateForms\Pages;

use App\Filament\Resources\GeoLocateForms\GeoLocateFormResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeoLocateForm extends EditRecord
{
    protected static string $resource = GeoLocateFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
