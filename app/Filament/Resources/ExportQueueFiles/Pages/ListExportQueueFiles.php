<?php

namespace App\Filament\Resources\ExportQueueFiles\Pages;

use App\Filament\Resources\ExportQueueFiles\ExportQueueFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExportQueueFiles extends ListRecords
{
    protected static string $resource = ExportQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
