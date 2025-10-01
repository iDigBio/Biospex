<?php

namespace App\Filament\Resources\ActorWorkflows\Pages;

use App\Filament\Resources\ActorWorkflows\ActorWorkflowResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActorWorkflow extends EditRecord
{
    protected static string $resource = ActorWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
