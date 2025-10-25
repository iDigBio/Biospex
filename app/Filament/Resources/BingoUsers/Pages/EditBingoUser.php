<?php

namespace App\Filament\Resources\BingoUsers\Pages;

use App\Filament\Resources\BingoUsers\BingoUserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBingoUser extends EditRecord
{
    protected static string $resource = BingoUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
