<?php

namespace App\Filament\Resources\TranscriptionLocations\Pages;

use App\Filament\Resources\TranscriptionLocations\TranscriptionLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTranscriptionLocations extends ListRecords
{
    protected static string $resource = TranscriptionLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
