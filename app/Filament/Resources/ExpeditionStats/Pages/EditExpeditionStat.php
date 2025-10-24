<?php

namespace App\Filament\Resources\ExpeditionStats\Pages;

use App\Filament\Resources\ExpeditionStats\ExpeditionStatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExpeditionStat extends EditRecord
{
    protected static string $resource = ExpeditionStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
