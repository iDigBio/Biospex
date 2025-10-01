<?php

namespace App\Filament\Resources\GeoLocateDataSources\Pages;

use App\Filament\Resources\GeoLocateDataSources\GeoLocateDataSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeoLocateDataSource extends EditRecord
{
    protected static string $resource = GeoLocateDataSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
