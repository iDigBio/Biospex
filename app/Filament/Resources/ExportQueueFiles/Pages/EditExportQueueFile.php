<?php

namespace App\Filament\Resources\ExportQueueFiles\Pages;

use App\Filament\Resources\ExportQueueFiles\ExportQueueFileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExportQueueFile extends EditRecord
{
    protected static string $resource = ExportQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
