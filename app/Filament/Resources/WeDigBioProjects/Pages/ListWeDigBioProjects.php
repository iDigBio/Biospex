<?php

namespace App\Filament\Resources\WeDigBioProjects\Pages;

use App\Filament\Resources\WeDigBioProjects\WeDigBioProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeDigBioProjects extends ListRecords
{
    protected static string $resource = WeDigBioProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
