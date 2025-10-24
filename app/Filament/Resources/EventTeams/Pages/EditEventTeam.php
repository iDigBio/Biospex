<?php

namespace App\Filament\Resources\EventTeams\Pages;

use App\Filament\Resources\EventTeams\EventTeamResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventTeam extends EditRecord
{
    protected static string $resource = EventTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
