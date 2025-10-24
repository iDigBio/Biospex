<?php

namespace App\Filament\Resources\ActorExpeditions\Pages;

use App\Filament\Resources\ActorExpeditions\ActorExpeditionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActorExpedition extends EditRecord
{
    protected static string $resource = ActorExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
