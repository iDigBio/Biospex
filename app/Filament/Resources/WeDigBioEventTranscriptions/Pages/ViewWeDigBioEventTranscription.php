<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions\Pages;

use App\Filament\Resources\WeDigBioEventTranscriptions\WeDigBioEventTranscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWeDigBioEventTranscription extends ViewRecord
{
    protected static string $resource = WeDigBioEventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
