<?php

namespace App\Filament\Resources\ImportOccurrences\Pages;

use App\Filament\Resources\ImportOccurrences\ImportOccurrenceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewImportOccurrence extends ViewRecord
{
    protected static string $resource = ImportOccurrenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
