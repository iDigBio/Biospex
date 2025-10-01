<?php

namespace App\Filament\Resources\BingoUsers\Pages;

use App\Filament\Resources\BingoUsers\BingoUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBingoUser extends CreateRecord
{
    protected static string $resource = BingoUserResource::class;
}
