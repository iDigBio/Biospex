<?php

namespace App\Filament\Resources\ImportOccurrences\Pages;

use App\Filament\Resources\ImportOccurrences\ImportOccurrenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportOccurrences extends ListRecords
{
    protected static string $resource = ImportOccurrenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
