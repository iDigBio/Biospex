<?php

namespace App\Filament\Resources\Expeditions\Pages;

use App\Filament\Resources\Expeditions\ExpeditionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExpedition extends ViewRecord
{
    protected static string $resource = ExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
