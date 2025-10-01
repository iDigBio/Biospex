<?php

namespace App\Filament\Resources\ExportQueues\Pages;

use App\Filament\Resources\ExportQueues\ExportQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExportQueue extends ViewRecord
{
    protected static string $resource = ExportQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
