<?php

namespace App\Filament\Resources\StateCounties\Pages;

use App\Filament\Resources\StateCounties\StateCountyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStateCounty extends ViewRecord
{
    protected static string $resource = StateCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
