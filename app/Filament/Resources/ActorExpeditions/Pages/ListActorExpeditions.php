<?php

namespace App\Filament\Resources\ActorExpeditions\Pages;

use App\Filament\Resources\ActorExpeditions\ActorExpeditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActorExpeditions extends ListRecords
{
    protected static string $resource = ActorExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
