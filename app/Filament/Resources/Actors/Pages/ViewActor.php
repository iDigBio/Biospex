<?php

namespace App\Filament\Resources\Actors\Pages;

use App\Filament\Resources\Actors\ActorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActor extends ViewRecord
{
    protected static string $resource = ActorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
