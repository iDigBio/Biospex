<?php

namespace App\Filament\Resources\ActorContacts\Pages;

use App\Filament\Resources\ActorContacts\ActorContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActorContacts extends ListRecords
{
    protected static string $resource = ActorContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
