<?php

namespace App\Filament\Resources\Updates\Pages;

use App\Filament\Resources\Updates\UpdateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUpdates extends ListRecords
{
    protected static string $resource = UpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
