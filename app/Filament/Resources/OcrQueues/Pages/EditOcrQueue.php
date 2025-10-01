<?php

namespace App\Filament\Resources\OcrQueues\Pages;

use App\Filament\Resources\OcrQueues\OcrQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOcrQueue extends EditRecord
{
    protected static string $resource = OcrQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
