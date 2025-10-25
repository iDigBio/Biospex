<?php

namespace App\Filament\Resources\ExportQueues\Pages;

use App\Filament\Resources\ExportQueues\ExportQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExportQueues extends ListRecords
{
    protected static string $resource = ExportQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
