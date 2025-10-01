<?php

namespace App\Filament\Resources\ExportQueues\Pages;

use App\Filament\Resources\ExportQueues\ExportQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExportQueue extends EditRecord
{
    protected static string $resource = ExportQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
