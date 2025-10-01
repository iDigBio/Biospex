<?php

namespace App\Filament\Resources\ActorWorkflows\Pages;

use App\Filament\Resources\ActorWorkflows\ActorWorkflowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActorWorkflows extends ListRecords
{
    protected static string $resource = ActorWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
