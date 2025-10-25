<?php

namespace App\Filament\Resources\BingoWords\Pages;

use App\Filament\Resources\BingoWords\BingoWordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBingoWord extends EditRecord
{
    protected static string $resource = BingoWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
