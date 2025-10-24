<?php

namespace App\Filament\Resources\WorkflowManagers\Pages;

use App\Filament\Resources\WorkflowManagers\WorkflowManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowManager extends EditRecord
{
    protected static string $resource = WorkflowManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
