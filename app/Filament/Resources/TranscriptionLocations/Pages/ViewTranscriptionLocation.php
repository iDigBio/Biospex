<?php

namespace App\Filament\Resources\TranscriptionLocations\Pages;

use App\Filament\Resources\TranscriptionLocations\TranscriptionLocationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTranscriptionLocation extends ViewRecord
{
    protected static string $resource = TranscriptionLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
