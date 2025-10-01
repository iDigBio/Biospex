<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions\Pages;

use App\Filament\Resources\WeDigBioEventTranscriptions\WeDigBioEventTranscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeDigBioEventTranscriptions extends ListRecords
{
    protected static string $resource = WeDigBioEventTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
