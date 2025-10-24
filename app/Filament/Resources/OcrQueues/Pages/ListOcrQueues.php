<?php

namespace App\Filament\Resources\OcrQueues\Pages;

use App\Filament\Resources\OcrQueues\OcrQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOcrQueues extends ListRecords
{
    protected static string $resource = OcrQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
