<?php

namespace App\Filament\Resources\BingoWords\Pages;

use App\Filament\Resources\BingoWords\BingoWordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBingoWord extends ViewRecord
{
    protected static string $resource = BingoWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
