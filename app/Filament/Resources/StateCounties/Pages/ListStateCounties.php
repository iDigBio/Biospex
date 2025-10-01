<?php

namespace App\Filament\Resources\StateCounties\Pages;

use App\Filament\Resources\StateCounties\StateCountyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStateCounties extends ListRecords
{
    protected static string $resource = StateCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
