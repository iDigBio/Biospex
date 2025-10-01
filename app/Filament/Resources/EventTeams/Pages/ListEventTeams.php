<?php

namespace App\Filament\Resources\EventTeams\Pages;

use App\Filament\Resources\EventTeams\EventTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventTeams extends ListRecords
{
    protected static string $resource = EventTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
