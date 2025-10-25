<?php

namespace App\Filament\Resources\EventUsers\Pages;

use App\Filament\Resources\EventUsers\EventUserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventUser extends ViewRecord
{
    protected static string $resource = EventUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
