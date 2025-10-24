<?php

namespace App\Filament\Resources\Bingos\Pages;

use App\Filament\Resources\Bingos\BingoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBingos extends ListRecords
{
    protected static string $resource = BingoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
