<?php

namespace App\Filament\Resources\Updates\Pages;

use App\Filament\Resources\Updates\UpdateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUpdate extends EditRecord
{
    protected static string $resource = UpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
