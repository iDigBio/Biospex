<?php

namespace App\Filament\Resources\EventTranscriptions\Pages;

use App\Filament\Resources\EventTranscriptions\EventTranscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventTranscription extends ViewRecord
{
    protected static string $resource = EventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
