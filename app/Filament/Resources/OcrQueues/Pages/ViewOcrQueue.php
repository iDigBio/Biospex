<?php

namespace App\Filament\Resources\OcrQueues\Pages;

use App\Filament\Resources\OcrQueues\OcrQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOcrQueue extends ViewRecord
{
    protected static string $resource = OcrQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
