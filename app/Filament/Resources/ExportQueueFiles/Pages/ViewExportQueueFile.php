<?php

namespace App\Filament\Resources\ExportQueueFiles\Pages;

use App\Filament\Resources\ExportQueueFiles\ExportQueueFileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExportQueueFile extends ViewRecord
{
    protected static string $resource = ExportQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
