<?php

namespace App\Filament\Resources\ImportOccurrences\Pages;

use App\Filament\Resources\ImportOccurrences\ImportOccurrenceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditImportOccurrence extends EditRecord
{
    protected static string $resource = ImportOccurrenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
