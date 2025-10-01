<?php

namespace App\Filament\Resources\PanoptesProjects\Pages;

use App\Filament\Resources\PanoptesProjects\PanoptesProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPanoptesProjects extends ListRecords
{
    protected static string $resource = PanoptesProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
