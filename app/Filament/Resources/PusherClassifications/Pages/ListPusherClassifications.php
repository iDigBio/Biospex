<?php

namespace App\Filament\Resources\PusherClassifications\Pages;

use App\Filament\Resources\PusherClassifications\PusherClassificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPusherClassifications extends ListRecords
{
    protected static string $resource = PusherClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
