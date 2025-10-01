<?php

namespace App\Filament\Resources\ActorContacts\Pages;

use App\Filament\Resources\ActorContacts\ActorContactResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActorContact extends ViewRecord
{
    protected static string $resource = ActorContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
