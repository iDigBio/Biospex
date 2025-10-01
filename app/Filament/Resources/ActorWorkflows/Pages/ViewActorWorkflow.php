<?php

namespace App\Filament\Resources\ActorWorkflows\Pages;

use App\Filament\Resources\ActorWorkflows\ActorWorkflowResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActorWorkflow extends ViewRecord
{
    protected static string $resource = ActorWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
