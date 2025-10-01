<?php

namespace App\Filament\Resources\ActorExpeditions\Pages;

use App\Filament\Resources\ActorExpeditions\ActorExpeditionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActorExpedition extends ViewRecord
{
    protected static string $resource = ActorExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
