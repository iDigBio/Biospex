<?php

namespace App\Filament\Resources\WeDigBioEvents\Pages;

use App\Filament\Resources\WeDigBioEvents\WeDigBioEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWeDigBioEvent extends ViewRecord
{
    protected static string $resource = WeDigBioEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
