<?php

namespace App\Filament\Resources\StateCounties\Pages;

use App\Filament\Resources\StateCounties\StateCountyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStateCounty extends EditRecord
{
    protected static string $resource = StateCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
