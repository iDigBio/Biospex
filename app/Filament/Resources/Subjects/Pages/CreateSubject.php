<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Data coming from form is already in correct format for MongoDB
        return $data;
    }
}
