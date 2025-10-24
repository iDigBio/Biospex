<?php

namespace App\Filament\Resources\EventTeams\Pages;

use App\Filament\Resources\EventTeams\EventTeamResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventTeam extends ViewRecord
{
    protected static string $resource = EventTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
