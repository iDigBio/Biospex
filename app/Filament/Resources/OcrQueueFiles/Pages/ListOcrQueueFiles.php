<?php

namespace App\Filament\Resources\OcrQueueFiles\Pages;

use App\Filament\Resources\OcrQueueFiles\OcrQueueFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOcrQueueFiles extends ListRecords
{
    protected static string $resource = OcrQueueFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
