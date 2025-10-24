<?php

namespace App\Filament\Resources\Headers\Pages;

use App\Filament\Resources\Headers\HeaderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHeader extends ViewRecord
{
    protected static string $resource = HeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
