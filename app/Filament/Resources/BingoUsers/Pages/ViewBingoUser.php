<?php

namespace App\Filament\Resources\BingoUsers\Pages;

use App\Filament\Resources\BingoUsers\BingoUserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBingoUser extends ViewRecord
{
    protected static string $resource = BingoUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
