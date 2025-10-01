<?php

namespace App\Filament\Resources\WorkflowManagers\Pages;

use App\Filament\Resources\WorkflowManagers\WorkflowManagerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkflowManager extends ViewRecord
{
    protected static string $resource = WorkflowManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
