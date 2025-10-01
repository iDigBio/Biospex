<?php

namespace App\Filament\Resources\EventTranscriptions\Pages;

use App\Filament\Resources\EventTranscriptions\EventTranscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventTranscription extends EditRecord
{
    protected static string $resource = EventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
