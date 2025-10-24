<?php

namespace App\Filament\Resources\TranscriptionLocations\Pages;

use App\Filament\Resources\TranscriptionLocations\TranscriptionLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTranscriptionLocation extends EditRecord
{
    protected static string $resource = TranscriptionLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
