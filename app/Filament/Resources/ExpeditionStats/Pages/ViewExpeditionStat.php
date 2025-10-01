<?php

namespace App\Filament\Resources\ExpeditionStats\Pages;

use App\Filament\Resources\ExpeditionStats\ExpeditionStatResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExpeditionStat extends ViewRecord
{
    protected static string $resource = ExpeditionStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
