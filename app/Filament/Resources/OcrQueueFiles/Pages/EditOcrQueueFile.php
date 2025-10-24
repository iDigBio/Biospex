<?php

namespace App\Filament\Resources\OcrQueueFiles\Pages;

use App\Filament\Resources\OcrQueueFiles\OcrQueueFileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOcrQueueFile extends EditRecord
{
    protected static string $resource = OcrQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
