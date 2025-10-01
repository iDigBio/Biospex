<?php

namespace App\Filament\Resources\Bingos\Pages;

use App\Filament\Resources\Bingos\BingoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBingo extends EditRecord
{
    protected static string $resource = BingoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
