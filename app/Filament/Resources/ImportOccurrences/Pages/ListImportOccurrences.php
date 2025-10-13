<?php

namespace App\Filament\Resources\ImportOccurrences\Pages;

use App\Filament\Resources\ImportOccurrences\ImportOccurrenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportOccurrences extends ListRecords
{
    protected static string $resource = ImportOccurrenceResource::class;

    protected ?string $subheading = 'This resource manages import occurrence records used during subject imports from DWC files. It\'s primarily intended for system administrators and developers.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
