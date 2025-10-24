<?php

namespace App\Filament\Resources\Imports\Pages;

use App\Filament\Resources\Imports\ImportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewImport extends ViewRecord
{
    protected static string $resource = ImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
