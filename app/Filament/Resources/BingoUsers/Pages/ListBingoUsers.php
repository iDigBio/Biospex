<?php

namespace App\Filament\Resources\BingoUsers\Pages;

use App\Filament\Resources\BingoUsers\BingoUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBingoUsers extends ListRecords
{
    protected static string $resource = BingoUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
