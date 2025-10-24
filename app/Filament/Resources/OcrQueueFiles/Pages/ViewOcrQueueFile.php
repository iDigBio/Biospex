<?php

namespace App\Filament\Resources\OcrQueueFiles\Pages;

use App\Filament\Resources\OcrQueueFiles\OcrQueueFileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOcrQueueFile extends ViewRecord
{
    protected static string $resource = OcrQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
