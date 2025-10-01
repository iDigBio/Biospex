<?php

namespace App\Filament\Resources\BingoWords\Pages;

use App\Filament\Resources\BingoWords\BingoWordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBingoWords extends ListRecords
{
    protected static string $resource = BingoWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
