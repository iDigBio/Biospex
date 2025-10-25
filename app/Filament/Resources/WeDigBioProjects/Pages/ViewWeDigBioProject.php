<?php

namespace App\Filament\Resources\WeDigBioProjects\Pages;

use App\Filament\Resources\WeDigBioProjects\WeDigBioProjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWeDigBioProject extends ViewRecord
{
    protected static string $resource = WeDigBioProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
