<?php

namespace App\Filament\Resources\EventUsers\Pages;

use App\Filament\Resources\EventUsers\EventUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventUsers extends ListRecords
{
    protected static string $resource = EventUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
