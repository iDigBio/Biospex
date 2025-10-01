<?php

namespace App\Filament\Resources\EventTranscriptions\Pages;

use App\Filament\Resources\EventTranscriptions\EventTranscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventTranscriptions extends ListRecords
{
    protected static string $resource = EventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
