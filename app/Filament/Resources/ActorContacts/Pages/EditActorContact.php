<?php

namespace App\Filament\Resources\ActorContacts\Pages;

use App\Filament\Resources\ActorContacts\ActorContactResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActorContact extends EditRecord
{
    protected static string $resource = ActorContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
