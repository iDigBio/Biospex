<?php

namespace App\Filament\Resources\PanoptesProjects\Pages;

use App\Filament\Resources\PanoptesProjects\PanoptesProjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPanoptesProject extends ViewRecord
{
    protected static string $resource = PanoptesProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
