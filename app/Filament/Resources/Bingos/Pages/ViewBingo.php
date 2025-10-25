<?php

namespace App\Filament\Resources\Bingos\Pages;

use App\Filament\Resources\Bingos\BingoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBingo extends ViewRecord
{
    protected static string $resource = BingoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
