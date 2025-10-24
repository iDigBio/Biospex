<?php

namespace App\Filament\Resources\WeDigBioProjects\Pages;

use App\Filament\Resources\WeDigBioProjects\WeDigBioProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWeDigBioProject extends EditRecord
{
    protected static string $resource = WeDigBioProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
