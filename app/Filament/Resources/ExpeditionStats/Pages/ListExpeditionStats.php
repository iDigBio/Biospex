<?php

namespace App\Filament\Resources\ExpeditionStats\Pages;

use App\Filament\Resources\ExpeditionStats\ExpeditionStatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpeditionStats extends ListRecords
{
    protected static string $resource = ExpeditionStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
