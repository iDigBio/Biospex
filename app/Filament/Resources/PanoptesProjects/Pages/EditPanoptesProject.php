<?php

namespace App\Filament\Resources\PanoptesProjects\Pages;

use App\Filament\Resources\PanoptesProjects\PanoptesProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPanoptesProject extends EditRecord
{
    protected static string $resource = PanoptesProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
