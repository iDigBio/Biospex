<?php

namespace App\Filament\Resources\Updates\Pages;

use App\Filament\Resources\Updates\UpdateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUpdate extends ViewRecord
{
    protected static string $resource = UpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
