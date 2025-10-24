<?php

namespace App\Filament\Resources\Expeditions\Pages;

use App\Filament\Resources\Expeditions\ExpeditionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExpedition extends EditRecord
{
    protected static string $resource = ExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
