<?php

namespace App\Filament\Resources\Actors\Pages;

use App\Filament\Resources\Actors\ActorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActors extends ListRecords
{
    protected static string $resource = ActorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
