<?php

namespace App\Filament\Resources\WorkflowManagers\Pages;

use App\Filament\Resources\WorkflowManagers\WorkflowManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowManagers extends ListRecords
{
    protected static string $resource = WorkflowManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
