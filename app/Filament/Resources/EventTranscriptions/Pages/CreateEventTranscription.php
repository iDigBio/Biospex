<?php

namespace App\Filament\Resources\EventTranscriptions\Pages;

use App\Filament\Resources\EventTranscriptions\EventTranscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventTranscription extends CreateRecord
{
    protected static string $resource = EventTranscriptionResource::class;
}
