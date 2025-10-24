<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions\Pages;

use App\Filament\Resources\WeDigBioEventTranscriptions\WeDigBioEventTranscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWeDigBioEventTranscription extends EditRecord
{
    protected static string $resource = WeDigBioEventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
