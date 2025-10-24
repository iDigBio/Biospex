<?php

namespace App\Filament\Resources\Headers\Pages;

use App\Filament\Resources\Headers\HeaderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHeader extends EditRecord
{
    protected static string $resource = HeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
